/*
  ========================================================================
  SIIS (Sistem Informasi Inventaris Sekolah) - IoT Firmware
  Target Board: ESP32 Dev Module
  RFID Reader : MFRC522 (SPI)
  Protocol    : HTTP POST JSON ke Endpoint /api/v1/rfid/verify
  ========================================================================
  
  Wiring ESP32 to MFRC522:
  - 3.3V   -> 3.3V (JANGAN 5V)
  - GND    -> GND
  - RST    -> GPIO 22
  - SDA/SS -> GPIO 21 (atau GPIO 5)
  - SCK    -> GPIO 18
  - MISO   -> GPIO 19
  - MOSI   -> GPIO 23
  
  Buzzer / LED (Opsional):
  - BUZZER -> GPIO 4
  - LED_OK -> GPIO 2
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h> // ArduinoJson v6 atau v7

// 1. Konfigurasi Jaringan WiFi
const char* ssid = "NAMA_WIFI_SEKOLAH";
const char* password = "PASSWORD_WIFI";

// 2. Konfigurasi Server SIIS
// Ganti dengan IP lokal komputer server Laravel (misal: http://192.168.1.50:8000/api/v1/rfid/verify)
const char* serverEndpoint = "http://192.168.1.100:8000/api/v1/rfid/verify";

// 3. Kredensial Perangkat IoT (Sesuai tabel iot_devices)
const char* deviceId = "ESP32-SARPRAS-01";
const char* apiKey   = "siis-rfid-secret-key-2026";

// 4. Pin Definition MFRC522
#define RST_PIN  22
#define SS_PIN   21
#define BUZZER_PIN 4
#define LED_PIN    2

MFRC522 rfid(SS_PIN, RST_PIN);

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n--- Memulai Inisialisasi SIIS RFID Reader ---");

  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_PIN, LOW);

  // Inisialisasi SPI & MFRC522
  SPI.begin();
  rfid.PCD_Init();
  delay(100);
  rfid.PCD_DumpVersionToSerial();

  // Koneksi ke WiFi
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\n[WiFi] Berhasil Terhubung!");
  Serial.print("[WiFi] IP Address: ");
  Serial.println(WiFi.localIP());

  // Indikasi Beep 2x saat siap
  beep(100); delay(100); beep(100);
  Serial.println(">>> Siap menerima tap kartu RFID...");
}

void loop() {
  // Cek apakah ada kartu RFID baru terdeteksi
  if (!rfid.PICC_IsNewCardPresent()) {
    return;
  }
  if (!rfid.PICC_ReadCardSerial()) {
    return;
  }

  // Baca UID kartu dan konversi ke string HEX huruf kapital
  String rfidUid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) {
      rfidUid += "0";
    }
    rfidUid += String(rfid.uid.uidByte[i], HEX);
  }
  rfidUid.toUpperCase();

  Serial.println("\n-------------------------------------------");
  Serial.print("[RFID] Kartu Ditempel! UID: ");
  Serial.println(rfidUid);

  // Indikator visual & suara tap kartu
  digitalWrite(LED_PIN, HIGH);
  beep(80);

  // Kirim data ke API Laravel SIIS
  kirimVerifikasiKeServer(rfidUid);

  digitalWrite(LED_PIN, LOW);

  // Hentikan enkripsi pada kartu & tunggu jeda sebelum tap berikutnya
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
  delay(1500);
}

void kirimVerifikasiKeServer(String uid) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[ERROR] WiFi terputus!");
    beepError();
    return;
  }

  HTTPClient http;
  http.begin(serverEndpoint);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");

  // Siapkan Payload JSON
  StaticJsonDocument<256> doc;
  doc["device_id"] = deviceId;
  doc["api_key"]   = apiKey;
  doc["rfid_uid"]  = uid;

  String jsonPayload;
  serializeJson(doc, jsonPayload);

  Serial.println("[HTTP] Mengirim POST ke SIIS Server...");
  int httpResponseCode = http.POST(jsonPayload);

  if (httpResponseCode > 0) {
    String responseString = http.getString();
    Serial.print("[HTTP] Response Code: ");
    Serial.println(httpResponseCode);
    Serial.print("[HTTP] Payload: ");
    Serial.println(responseString);

    StaticJsonDocument<512> respDoc;
    DeserializationError error = deserializeJson(respDoc, responseString);

    if (!error) {
      const char* status = respDoc["status"];
      const char* message = respDoc["message"];

      Serial.print("[SIIS] Status: ");
      Serial.println(status);
      Serial.print("[SIIS] Pesan: ");
      Serial.println(message);

      if (strcmp(status, "borrow_confirmed") == 0) {
        // Konfirmasi pengambilan alat berhasil!
        beepSuccess();
      } else if (strcmp(status, "active_loan_detected") == 0 || strcmp(status, "user_identified") == 0) {
        // User teridentifikasi normal
        beep(150);
      } else {
        beepError();
      }
    }
  } else {
    Serial.print("[HTTP] Gagal mengirim data, error: ");
    Serial.println(http.errorToString(httpResponseCode).c_str());
    beepError();
  }

  http.end();
}

void beep(int ms) {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(ms);
  digitalWrite(BUZZER_PIN, LOW);
}

void beepSuccess() {
  beep(100); delay(80);
  beep(100); delay(80);
  beep(250);
}

void beepError() {
  beep(400); delay(150);
  beep(400);
}

