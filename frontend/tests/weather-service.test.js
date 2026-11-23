const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const vm = require("node:vm");

const source = fs.readFileSync(path.join(__dirname, "../js/services/weather-service.js"), "utf8");

function createService(fetchImplementation, baseUrl = "http://127.0.0.1:8000/api/v1") {
  const window = { setTimeout, clearTimeout };
  const context = {
    AbortController,
    URL,
    document: { querySelector: () => ({ content: baseUrl }) },
    fetch: fetchImplementation,
    window
  };
  vm.runInNewContext(source, context);
  return window.EasyBitesWeatherService;
}

test("requests and returns the Sarajevo weather payload", async () => {
  let requestedUrl;
  const service = createService(async (url, options) => {
    requestedUrl = String(url);
    assert.equal(options.headers.Accept, "application/json");
    assert.ok(options.signal instanceof AbortSignal);
    return {
      ok: true,
      json: async () => ({
        data: {
          location: { name: "Sarajevo" },
          current: {
            condition: "Partly cloudy",
            temperature_c: 14.2,
            feels_like_c: 13,
            humidity_percent: 60,
            precipitation_mm: 0,
            wind_speed_kmh: 5
          },
          dining_tip: "A local favourite sounds good."
        }
      })
    };
  });

  const result = await service.getSarajevoWeather();
  assert.equal(requestedUrl, "http://127.0.0.1:8000/api/v1/weather/sarajevo");
  assert.equal(result.location.name, "Sarajevo");
  assert.equal(result.dining_tip, "A local favourite sounds good.");
});

test("surfaces API errors without treating them as successful data", async () => {
  const service = createService(async () => ({
    ok: false,
    json: async () => ({ error: "Current Sarajevo weather is temporarily unavailable." })
  }));
  await assert.rejects(service.getSarajevoWeather(), /temporarily unavailable/);
});

test("rejects malformed payloads and network failures", async () => {
  const malformedService = createService(async () => ({
    ok: true,
    json: async () => ({ data: { current: {}, dining_tip: "Try a local dish." } })
  }));
  await assert.rejects(malformedService.getSarajevoWeather(), /unexpected response/);

  const offlineService = createService(async () => {
    throw new Error("offline");
  });
  await assert.rejects(offlineService.getSarajevoWeather(), /offline/);
});
