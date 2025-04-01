const express = require("express");
const mysql = require("mysql2");
const http = require("http");
const { Server } = require("socket.io");
const { SerialPort } = require("serialport");
const { ReadlineParser } = require("@serialport/parser-readline");
const twilio = require("twilio");
const path = require("path");
const fs = require("fs");

// Set debug flag
const DEBUG = true;

const phpExpress = require("php-express")({
    binPath: "C:\\wamp64\\bin\\php\\php7.4.33\\php.exe"
});

const app = express();
const server = http.createServer(app);

// Updated Socket.IO initialization
const io = new Server(server, {
    cors: {
        origin: "*", // In production, specify actual origins
        methods: ["GET", "POST"]
    },
    transports: ['polling', 'websocket'],
    allowEIO3: true // Allow compatibility with both EIO3 and EIO4 clients
});

// Add error handling for Socket.IO server
io.engine.on("connection_error", (err) => {
    console.log(`Socket.IO connection error: ${err.message}`);
});

// Add this middleware to ensure proper Content-Type headers
app.use((req, res, next) => {
    res.header("Content-Type", "application/json");
    next();
});

// Set PHP as the rendering engine
app.set("views", path.join(__dirname, "../pages/"));
app.engine("php", phpExpress.engine);
app.set("view engine", "php");

// Serve static files
app.use(express.static(path.join(__dirname, "../pages")));

// Route to render PHP files with enhanced error handling
app.get("/", (req, res) => {
    if (DEBUG) console.log("🔍 Landing page route accessed");
    
    // Check if the landing page file exists
    const viewsDir = path.join(__dirname, "../pages/");
    const landingPagePath = path.join(viewsDir, "landingpage.php");
    const mainMapPagePath = path.join(viewsDir, "caintamappage.php");
    const brgyMapPagePath = path.join(viewsDir, "BRGYcaintamappage.php");
    
    if (DEBUG) {
        console.log("🔍 Looking for landing pages at:", {
            landingPage: landingPagePath,
            mainMapPage: mainMapPagePath,
            brgyMapPage: brgyMapPagePath
        });
        
        // List all files in the pages directory to verify
        try {
            const files = fs.readdirSync(viewsDir);
            console.log("📁 Files in pages directory:", files);
        } catch (err) {
            console.error("❌ Cannot read pages directory:", err.message);
        }
    }
    
    // Check which files exist and attempt to render accordingly
    const renderOptions = {};
    
    // Try to render landing page first
    if (fs.existsSync(landingPagePath)) {
        if (DEBUG) console.log("✅ Landing page file found!");
        renderOptions.landingPage = "landingpage.php";
    }
    
    // Check main map page
    if (fs.existsSync(mainMapPagePath)) {
        if (DEBUG) console.log("✅ Main map page found!");
        renderOptions.mainMapPage = "caintamappage.php";
    }
    
    // Check BRGY map page
    if (fs.existsSync(brgyMapPagePath)) {
        if (DEBUG) console.log("✅ BRGY map page found!");
        renderOptions.brgyMapPage = "BRGYcaintamappage.php";
    }
    
    // If no pages found, return an error
    if (Object.keys(renderOptions).length === 0) {
        console.error("❌ No PHP page files found");
        return res.status(404).send("<h1>Landing page not found</h1><p>No PHP files exist at the expected location. Check server logs for details.</p>");
    }
    
    // Attempt to render the first available page
    const pageToRender = renderOptions.landingPage || 
                         renderOptions.mainMapPage || 
                         renderOptions.brgyMapPage;
    
    try {
        if (DEBUG) console.log(`🔄 Attempting to render ${pageToRender}`);
        res.render(pageToRender, function(err, html) {
            if (err) {
                console.error(`❌ Error rendering ${pageToRender}:`, err);
                return res.status(500).send(`<h1>PHP Rendering Error</h1><p>There was an error rendering ${pageToRender}. Check server logs for details.</p>`);
            }
            
            if (DEBUG) console.log(`✅ ${pageToRender} rendered successfully`);
            res.end(html);
        });
    } catch (error) {
        console.error("❌ Exception during rendering:", error);
        res.status(500).send("<h1>Server Error</h1><p>There was an error processing your request. Check server logs for details.</p>");
    }
});

// MySQL Database Connection
const connection = mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "ialertdb"
});
  
connection.connect(err => {
    if (err) {
        console.error("❌ Database Connection Failed:", err);
    } else {
        console.log("✅ Database Connected Successfully");
    }
});

// API to Fetch Latest Data for Web Page - fixed query to avoid SQL duplicate error
app.get("/api/waterlevel", (req, res) => {
    const query = `
        SELECT a.brgyName, a.waterLevel, a.temperature, a.humidity, a.timestamp
        FROM alerttb a
        INNER JOIN (
            SELECT brgyName, MAX(timestamp) as max_time
            FROM alerttb
            GROUP BY brgyName
        ) b ON a.brgyName = b.brgyName AND a.timestamp = b.max_time
    `;

    connection.query(query, (err, results) => {
        if (err) {
            console.error("❌ Fetch Error:", err);
            return res.status(500).json({ error: "Database Fetch Error" });
        }
        
        // Water level thresholds and colors
        const alertLevels = {
            safe: { threshold: 15, color: "green" },
            warning: { threshold: 29, color: "yellow" },
            danger: { threshold: 30, color: "red" }
        };
        
        const data = {};
        results.forEach(row => {
            // Determine alert color based on water level
            let alertColor = alertLevels.safe.color;
            if (row.waterLevel >= alertLevels.danger.threshold) {
                alertColor = alertLevels.danger.color;
            } else if (row.waterLevel >= alertLevels.warning.threshold) {
                alertColor = alertLevels.warning.color;
            }
            
            data[row.brgyName] = {
                waterLevel: row.waterLevel,
                temperature: row.temperature,
                humidity: row.humidity,
                timestamp: row.timestamp,
                alertColor: alertColor
            };
        });
        
        // Set proper content type header
        res.setHeader('Content-Type', 'application/json');
        res.json(data);
    });
});

app.get("/api/test-sms", async (req, res) => {
    const testBarangay = req.query.barangay || "Test Barangay";
    const testLevel = req.query.level || 35;
    
    // Twilio Setup - Using correct credentials
    const TWILIO_ACCOUNT_SID = "AC6f0d21f0d040e2360586989ea5710c97";
    const TWILIO_AUTH_TOKEN = "e5465d84e315a003719f253d2768c3dc";
    const TWILIO_PHONE = "+19702334730";
    
    const twilioClient = twilio(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
    
    try {
        // Fetch recipient phone numbers from database
        const recipientPhones = await fetchPhoneNumbersFromDB(testBarangay);
        
        if (recipientPhones.length === 0) {
            return res.status(404).json({
                success: false,
                message: `No recipients found for ${testBarangay}`
            });
        }
        
        // Send SMS to all recipients
        const smsPromises = recipientPhones.map(phone => 
            sendAlertSMS(testBarangay, testLevel, phone, twilioClient, TWILIO_PHONE)
        );
        
        await Promise.all(smsPromises);
        
        res.json({ 
            success: true, 
            message: `Test SMS sent to ${recipientPhones.length} recipients for ${testBarangay} with water level ${testLevel}cm`
        });
    } catch (error) {
        console.error("Error in SMS test endpoint:", error);
        res.status(500).json({
            success: false,
            message: "Failed to send test SMS",
            error: error.message
        });
    }
});
// Add Twilio credentials test route
app.get("/api/test-twilio", (req, res) => {
    const TWILIO_ACCOUNT_SID = "AC6f0d21f0d040e2360586989ea5710c97";
    const TWILIO_AUTH_TOKEN = "e5465d84e315a003719f253d2768c3dc";
    const twilioClient = twilio(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
    
    twilioClient.api.accounts(TWILIO_ACCOUNT_SID).fetch()
        .then(account => {
            res.json({
                success: true,
                status: account.status,
                message: "Twilio credentials working properly"
            });
        })
        .catch(err => {
            res.json({
                success: false,
                error: err.message,
                message: "Twilio credentials failed"
            });
        });
});
// Add route to check phone numbers in database
app.get("/api/check-recipients/:barangay", (req, res) => {
    const barangay = req.params.barangay;
    fetchPhoneNumbersFromDB(barangay)
        .then(phones => {
            res.json({
                success: true,
                count: phones.length,
                phones: phones
            });
        })
        .catch(err => {
            res.json({
                success: false,
                error: err.message
            });
        });
});

// Add route to force an alert for testing
app.get("/api/force-alert/:barangay/:level", async (req, res) => {
    const testData = {
        barangay: req.params.barangay,
        waterLevel: parseFloat(req.params.level),
        temperature: 30,
        humidity: 80
    };
    
    try {
        await checkAndSendAlert(testData);
        res.json({
            success: true,
            message: `Alert check triggered for ${testData.barangay} with water level ${testData.waterLevel}cm`
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: "Error triggering alert",
            error: error.message
        });
    }
});
// Start Server - make sure this is only called once!
server.listen(3000, () => {
    console.log("🚀 Server running at http://localhost:3000");
    console.log("📊 API endpoint available at http://localhost:3000/api/waterlevel");
});

// Handle WebSocket Connections
io.on("connection", (socket) => {
    console.log("🔗 New WebSocket Connection:", socket.id);
    
    // Water level thresholds and colors
    const alertLevels = {
        safe: { threshold: 15, color: "green" },
        warning: { threshold: 29, color: "yellow" },
        danger: { threshold: 30, color: "red" }
    };
    
    // Send current data to new connections
    connection.query(`
        SELECT a.brgyName, a.waterLevel, a.temperature, a.humidity, a.timestamp
        FROM alerttb a
        INNER JOIN (
            SELECT brgyName, MAX(timestamp) as max_time
            FROM alerttb
            GROUP BY brgyName
        ) b ON a.brgyName = b.brgyName AND a.timestamp = b.max_time
    `, (err, results) => {
        if (!err && results.length > 0) {
            results.forEach(row => {
                // Determine alert color
                let alertColor = alertLevels.safe.color;
                if (row.waterLevel >= alertLevels.danger.threshold) {
                    alertColor = alertLevels.danger.color;
                } else if (row.waterLevel >= alertLevels.warning.threshold) {
                    alertColor = alertLevels.warning.color;
                }
                
                const data = {
                    barangay: row.brgyName,
                    waterLevel: row.waterLevel,
                    temperature: row.temperature,
                    humidity: row.humidity,
                    timestamp: row.timestamp,
                    alertColor: alertColor
                };
                
                socket.emit("sensorData", data);
            });
        }
    });
    
    socket.on("disconnect", () => {
        console.log("🔌 WebSocket Disconnected:", socket.id);
    });
});

// Global variables to store temperature and humidity from the first Arduino
let globalTemperature = 0; // Default temperature
let globalHumidity = 0;    // Default humidity

// Setup Arduino devices with updated barangay names
const arduinoDevices = [
    { port: "COM3", barangay: "San Juan", isTempHumiditySource: true },
    { port: "COM6", barangay: "San Andres", isTempHumiditySource: false },
    { port: "COM7", barangay: "San Isidro", isTempHumiditySource: false }
];

// Initialize all Arduino devices
arduinoDevices.forEach(device => {
    try {
        const serialPort = new SerialPort({ path: device.port, baudRate: 9600 });
        const parser = serialPort.pipe(new ReadlineParser({ delimiter: "\n" }));
        
        console.log(`✅ Connected to Arduino at ${device.port} for ${device.barangay}`);
        
        // Setup data handling for each Arduino
        parser.on("data", (data) => {
            try {
                data = data.trim();
                if (data.startsWith("{") && data.endsWith("}")) {
                    const jsonData = JSON.parse(data);

                    jsonData.waterLevel = parseFloat(jsonData.waterLevel);
                    console.log(`📊 Water level for ${device.barangay}: ${jsonData.waterLevel}cm (${typeof jsonData.waterLevel})`);
                    
                    // Assign the barangay from the device configuration
                    jsonData.barangay = device.barangay;
                    
                    // If this is the temperature/humidity source, update global values
                    if (device.isTempHumiditySource) {
                        globalTemperature = jsonData.temperature || globalTemperature;
                        globalHumidity = jsonData.humidity || globalHumidity;
                        console.log(`🌡️ Updated global temp/humidity from ${device.barangay}: ${globalTemperature}°C/${globalHumidity}%`);
                    } else {
                        // For non-source devices, use the global temperature and humidity
                        jsonData.temperature = globalTemperature;
                        jsonData.humidity = globalHumidity;
                    }
                    
                    console.log(`✅ Received from ${device.port} (${jsonData.barangay}):`, jsonData);
                    
                    // Store data in database - ensure all values are properly set
                    const query = "INSERT INTO alerttb (brgyName, waterLevel, temperature, humidity) VALUES (?, ?, ?, ?)";
                    connection.query(query, [
                        jsonData.barangay, 
                        jsonData.waterLevel, 
                        jsonData.temperature, 
                        jsonData.humidity
                    ], (err) => {
                        if (err) {
                            console.error(`❌ Database Insert Error for ${jsonData.barangay}:`, err);
                        } else {
                            console.log(`✅ Data Inserted Successfully for ${jsonData.barangay}`);
                        }
                    });
                    
                    // Handle errors in Arduino data
                    if (jsonData.error) {
                        console.log(`⚠️ Arduino Error from ${device.barangay}: ${jsonData.error}`);
                        return; // Skip emitting error data to clients
                    }
                    
                    // Determine alert color based on water level
                    const alertLevels = {
                        safe: { threshold: 15, color: "green" },
                        warning: { threshold: 29, color: "yellow" },
                        danger: { threshold: 30, color: "red" }
                    };
                    
                    let alertColor = alertLevels.safe.color;
                    if (jsonData.waterLevel >= alertLevels.danger.threshold) {
                        alertColor = alertLevels.danger.color;
                    } else if (jsonData.waterLevel >= alertLevels.warning.threshold) {
                        alertColor = alertLevels.warning.color;
                    }
                    
                    // Add alert color to the data
                    jsonData.alertColor = alertColor;
                    
                    // Emit to all connected web clients
                    io.emit("sensorData", jsonData);
                    
                    // Check alert threshold and send SMS if needed
                    checkAndSendAlert(jsonData);
                    
                } else {
                    console.log(`⚠️ Non-JSON Data from ${device.port}:`, data);
                }
            } catch (error) {
                console.error(`❌ JSON Parsing Error from ${device.port}:`, error.message, "| Data:", data);
            }
        });
        
        // Handle port errors
        serialPort.on('error', (err) => {
            console.error(`❌ Serial Port Error on ${device.port}:`, err.message);
        });
        
    } catch (error) {
        console.error(`❌ Failed to initialize Arduino at ${device.port}:`, error.message);
    }
});

// Track sent alerts to prevent duplicate messages
const sentAlerts = {};

// Function to check and send alerts with selective SMS based on water level
async function checkAndSendAlert(data) {
    const { barangay, waterLevel } = data;
    
    console.log(`🔔 Checking alert for ${barangay} with water level ${waterLevel}cm`);
    // Water level thresholds and colors
    const alertLevels = {
        safe: { threshold: 15, color: "blue", sendAlert: false },
        warning: { threshold: 29, color: "yellow", sendAlert: true },
        danger: { threshold: 30, color: "red", sendAlert: true }
    };
    
    // Determine alert level
    let alertStatus = "safe";
    let shouldSendAlert = false;
    
    if (waterLevel >= alertLevels.danger.threshold) {
        alertStatus = "danger";
        shouldSendAlert = alertLevels.danger.sendAlert;
    } else if (waterLevel >= alertLevels.warning.threshold) {
        alertStatus = "warning";
        shouldSendAlert = alertLevels.warning.sendAlert;
    }
    
    // Add color information to the data
    data.alertColor = alertLevels[alertStatus].color;
    
    // Only send SMS for warning or danger levels
    if (shouldSendAlert) {
        // Check if we sent an alert for this barangay recently (within 30 minutes)
        const now = Date.now();
const lastAlertTime = sentAlerts[barangay] || 0;
const thirtyMinutes = 30 * 60 * 1000;

if (true) {  // Always send alert for testing
    console.log(`🧪 TEST MODE: Sending alert regardless of timing`); {
            try {
                // Fetch phone numbers for the specific barangay
                const recipientPhones = await fetchPhoneNumbersFromDB(barangay);
                
                if (recipientPhones.length === 0) {
                    console.log(`❌ No recipients found for ${barangay}`);
                    return;
                }
                
                // Twilio Setup - Using the correct credentials
                const TWILIO_ACCOUNT_SID = "";
                const TWILIO_AUTH_TOKEN = "";
                const TWILIO_PHONE = "";
                
                const twilioClient = twilio(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
                
                // Prepare alert message with status
                const alertMessage = `🚨 ${alertStatus.toUpperCase()} ALERT: Water level in ${barangay} has reached ${waterLevel} cm! Please stay vigilant and prepare for potential evacuation.`;
                
                // Send alert to all recipients
                const smsPromises = recipientPhones.map(phone => 
                    sendAlertSMS(barangay, waterLevel, phone, twilioClient, TWILIO_PHONE, alertMessage, alertStatus)
                );
                
                await Promise.all(smsPromises);
                
                // Update last alert time
                sentAlerts[barangay] = now;
                
                // Log the alert for monitoring
                console.log(`📢 ${alertStatus.toUpperCase()} Alert sent for ${barangay} at ${waterLevel} cm`);
                
            } catch (error) {
                console.error(`❌ Error processing alerts for ${barangay}:`, error);
            }
        }
    } else {
            console.log(`⏱️ Skipping duplicate alert for ${barangay} (sent within last 30 minutes)`);
        }
    }
}

// Function to fetch phone numbers from database
async function fetchPhoneNumbersFromDB(barangay) {
    return new Promise((resolve, reject) => {
        const query = `
            SELECT contact FROM residentinfo 
            WHERE barangay = ? AND contact IS NOT NULL AND LENGTH(TRIM(contact)) > 0
        `;
        
        connection.query(query, [barangay], (err, results) => {
            if (err) {
                console.error("Database query error:", err);
                reject(err);
            } else {
                // Map results to phone numbers and ensure they start with +63
                const phoneNumbers = results.map(row => {
                    let phone = row.contact.trim();
                    
                    // Standardize phone number format
                    if (phone.startsWith('09')) {
                        phone = '+63' + phone.slice(1);
                    } else if (phone.startsWith('9')) {
                        phone = '+63' + phone;
                    }
                    
                    return phone;
                });
                
                console.log(`📱 Found ${phoneNumbers.length} recipients for ${barangay}:`, phoneNumbers);
                resolve(phoneNumbers);
            }
        });
    });
}

// Function to send SMS Alert with enhanced message and record in database
function sendAlertSMS(barangay, waterLevel, phoneNumber, twilioClient, fromPhone, alertMessage, alertStatus) {
    const message = alertMessage || `🚨 ${alertStatus ? alertStatus.toUpperCase() + ' ' : ''}ALERT: Water level in ${barangay} has reached ${waterLevel} cm! Please stay vigilant and prepare for potential evacuation.`;
    
    return twilioClient.messages
        .create({
            body: message,
            from: fromPhone,
            to: phoneNumber
        })
        .then(msg => {
            console.log(`📩 SMS Sent to ${phoneNumber} for ${barangay}: ${msg.sid}`);
            
            // Record successful message delivery in database
            const insertQuery = `INSERT INTO msgtb (waterLevel, message, barangay, status, timestamp) 
                                 VALUES (?, ?, ?, ?, NOW())`;
            
            connection.query(insertQuery, [
                waterLevel,
                message,
                barangay,
                'delivered'
            ], (err, result) => {
                if (err) {
                    console.error(`❌ Failed to record message delivery in database:`, err);
                } else {
                    console.log(`✅ Message delivery recorded in database for ${barangay}`);
                }
            });
            
            return msg;
        })
        .catch(err => {
            console.error(`❌ SMS Error to ${phoneNumber} for ${barangay}:`, err);
            
            // Record failed message delivery in database
            const insertQuery = `INSERT INTO msgtb (waterLevel, message, barangay, status, timestamp) 
                                 VALUES (?, ?, ?, ?, NOW())`;
            
            connection.query(insertQuery, [
                waterLevel,
                message,
                barangay,
                'not sent'
            ], (err, result) => {
                if (err) {
                    console.error(`❌ Failed to record failed message in database:`, err);
                } else {
                    console.log(`✅ Failed message recorded in database for ${barangay}`);
                }
            });
            
            throw err;
        });
}