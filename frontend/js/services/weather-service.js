(() => {
  const configuredBaseUrl = document.querySelector('meta[name="easybites-api-base-url"]')?.content.trim();
  const apiBaseUrl = configuredBaseUrl || "http://127.0.0.1:8000/api/v1";

  async function getSarajevoWeather() {
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 8000);

    try {
      const response = await fetch(new URL("weather/sarajevo", `${apiBaseUrl.replace(/\/+$/, "")}/`), {
        headers: { Accept: "application/json" },
        signal: controller.signal
      });
      const result = await response.json();
      if (!response.ok) {
        throw new Error(typeof result.error === "string" ? result.error : "Weather details are unavailable right now.");
      }

      const weather = result?.data;
      const current = weather?.current;
      const numericFields = ["temperature_c", "feels_like_c", "humidity_percent", "precipitation_mm", "wind_speed_kmh"];
      if (
        typeof weather?.location?.name !== "string" ||
        typeof weather.dining_tip !== "string" ||
        typeof current?.condition !== "string" ||
        numericFields.some((field) => !Number.isFinite(current[field]))
      ) {
        throw new Error("The weather service returned an unexpected response.");
      }
      return weather;
    } finally {
      window.clearTimeout(timeout);
    }
  }

  window.EasyBitesWeatherService = { getSarajevoWeather };
})();
