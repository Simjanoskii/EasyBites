const restaurants = [
  { id: 1, name: "Baščaršija Bites", cuisine: "Bosnian · Local classics", category: "Local", rating: "4.9", time: "20–30 min", fee: "KM 1.50", badge: "LOCAL GEM", emoji: "🥙", art: "terracotta", menu: [{ name: "Sarajevo ćevapi", description: "Grilled ćevapi, warm somun, onion & kajmak", price: 12.5 }, { name: "Begova čorba", description: "Slow-cooked chicken soup, just like home", price: 7 }, { name: "Ayran", description: "Cool, creamy yoghurt drink", price: 2.5 }] },
  { id: 2, name: "Zelena Bašta", cuisine: "Fresh · Bowls & salads", category: "Healthy", rating: "4.8", time: "25–35 min", fee: "KM 1.00", badge: "FRESH PICK", emoji: "🥗", art: "sage", menu: [{ name: "Garden bowl", description: "Seasonal greens, grains, roasted vegetables", price: 11 }, { name: "Avocado toast", description: "Sourdough, smashed avocado & herbs", price: 9.5 }, { name: "Green juice", description: "Apple, spinach, lemon & ginger", price: 5 }] },
  { id: 3, name: "Slice of Sarajevo", cuisine: "Italian · Pizza", category: "Pizza", rating: "4.7", time: "25–40 min", fee: "KM 2.00", badge: "CROWD FAVOURITE", emoji: "🍕", art: "yellow", menu: [{ name: "Margherita", description: "San Marzano tomato, mozzarella & basil", price: 12 }, { name: "Sarajevo special", description: "Smoky beef, mushrooms, local cheese", price: 16 }, { name: "Garlic knots", description: "Fresh-baked dough with garlic butter", price: 5 }] },
  { id: 4, name: "Mali Burger", cuisine: "Burgers · Comfort food", category: "Burgers", rating: "4.8", time: "20–35 min", fee: "KM 1.50", badge: "LOCAL GEM", emoji: "🍔", art: "rose", menu: [{ name: "The Mali", description: "Smash patty, aged cheese, house sauce", price: 13 }, { name: "Crispy chicken burger", description: "Buttermilk chicken, slaw & pickles", price: 12 }, { name: "Skin-on fries", description: "Crispy fries with a pinch of paprika", price: 4.5 }] },
  { id: 5, name: "Slatki Kutak", cuisine: "Bakery · Sweet treats", category: "Dessert", rating: "4.9", time: "15–25 min", fee: "KM 1.00", badge: "SWEET SPOT", emoji: "🍰", art: "violet", menu: [{ name: "Tufahija", description: "Poached apple, walnuts & whipped cream", price: 6 }, { name: "Baklava box", description: "Four pieces of honeyed, flaky baklava", price: 8 }, { name: "Bosnian coffee", description: "Traditional coffee, served with rahat lokum", price: 4 }] },
  { id: 6, name: "Neretva Grill", cuisine: "Grill · Balkan favourites", category: "Local", rating: "4.7", time: "30–40 min", fee: "KM 2.00", badge: "HEARTY & GOOD", emoji: "🍢", art: "blue", menu: [{ name: "Mixed grill for one", description: "A little taste of everything, grilled fresh", price: 18 }, { name: "Pljeskavica", description: "Balkan beef patty, somun & kajmak", price: 13 }, { name: "Shopska salad", description: "Tomato, cucumber & grated white cheese", price: 6 }] }
];

const pageSections = new Map(
  [...document.querySelectorAll("main#spapp > section[data-load]")]
    .map((section) => [section.id.replace(/^page-/, ""), section])
);
const pageNames = [...pageSections.keys()];
const viewLoads = new Map();
const cart = new Map();
const favoriteRestaurantIds = new Set();
let selectedRestaurant = restaurants[0];
let selectedCategory = "All";
let searchTerm = "";
let authMode = "login";
let toastTimer;
let weatherStarted = false;
let activePage = null;
let navigationSequence = 0;

const cartCount = document.querySelector("#cart-count");
const toast = document.querySelector("#toast");

async function loadPageView(page) {
  const section = pageSections.get(page);
  if (!section) return false;
  if (section.dataset.loaded === "true") return true;
  if (viewLoads.has(page)) return viewLoads.get(page);

  section.setAttribute("aria-busy", "true");
  section.innerHTML = '<p class="page-loading" role="status">Loading this view…</p>';
  const request = fetch(new URL(section.dataset.load, new URL("views/", document.baseURI)), {
    headers: { Accept: "text/html" }
  }).then(async (response) => {
    if (!response.ok) throw new Error(`View request failed with status ${response.status}.`);
    const markup = await response.text();
    if (!markup.trim()) throw new Error("The requested view is empty.");
    section.innerHTML = markup;
    section.dataset.loaded = "true";
    return true;
  }).catch((error) => {
    console.error(`Could not load the ${page} view.`, error);
    section.innerHTML = `
      <div class="page-load-error" role="alert">
        <p>This page could not be loaded. Check your connection and try again.</p>
        <button class="primary-button" type="button" data-retry-view="${page}">Try again</button>
      </div>`;
    return false;
  }).finally(() => {
    section.setAttribute("aria-busy", "false");
    viewLoads.delete(page);
  });
  viewLoads.set(page, request);
  return request;
}

function showWeatherError() {
  const status = document.querySelector("#weather-status");
  const content = document.querySelector("#weather-content");
  const error = document.querySelector("#weather-error");
  if (!status || !content || !error) return;
  status.textContent = "";
  content.classList.add("hidden");
  error.classList.remove("hidden");
}

async function loadWeather() {
  const status = document.querySelector("#weather-status");
  const content = document.querySelector("#weather-content");
  const error = document.querySelector("#weather-error");
  const retry = document.querySelector("#weather-retry");
  if (!status || !content || !error || !retry) return;

  status.textContent = "Checking the latest conditions…";
  content.classList.add("hidden");
  error.classList.add("hidden");
  retry.disabled = true;

  try {
    const weather = await window.EasyBitesWeatherService.getSarajevoWeather();
    document.querySelector("#weather-condition").textContent = weather.current.condition;
    document.querySelector("#weather-temperature").textContent = `${weather.current.temperature_c}°C`;
    document.querySelector("#weather-feels-like").textContent = `${weather.current.feels_like_c}°C`;
    document.querySelector("#weather-rain").textContent = `${weather.current.precipitation_mm} mm`;
    document.querySelector("#weather-wind").textContent = `${weather.current.wind_speed_kmh} km/h`;
    document.querySelector("#weather-dining-tip").textContent = weather.dining_tip;
    status.textContent = `Current conditions in ${weather.location.name}.`;
    content.classList.remove("hidden");
  } catch {
    showWeatherError();
  } finally {
    retry.disabled = false;
  }
}

function formatPrice(price) {
  return `KM ${price.toFixed(2)}`;
}

function showToast(message) {
  toast.textContent = message;
  toast.classList.add("visible");
  window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => toast.classList.remove("visible"), 2300);
}

function renderRestaurants() {
  const restaurantGrid = document.querySelector("#restaurant-grid");
  if (!restaurantGrid) return;

  const visibleRestaurants = restaurants.filter((restaurant) => {
    const matchesCategory = selectedCategory === "All" || restaurant.category === selectedCategory;
    const searchable = `${restaurant.name} ${restaurant.cuisine} ${restaurant.menu.map((item) => item.name).join(" ")}`.toLowerCase();
    return matchesCategory && searchable.includes(searchTerm);
  });

  restaurantGrid.innerHTML = visibleRestaurants.map((restaurant) => {
    const isFavorite = favoriteRestaurantIds.has(restaurant.id);
    return `
      <article class="restaurant-card">
        <a class="restaurant-card-link" href="#restaurant" data-restaurant="${restaurant.id}" aria-label="View ${restaurant.name} menu">
          <div class="restaurant-image image-${restaurant.art}">
            <span class="restaurant-badge">${restaurant.badge}</span>
            <span class="food-emoji" aria-hidden="true">${restaurant.emoji}</span>
          </div>
          <div class="restaurant-info">
            <div class="restaurant-title-line"><h3>${restaurant.name}</h3><span class="rating"><span>★</span>${restaurant.rating}</span></div>
            <p class="restaurant-cuisine">${restaurant.cuisine}</p>
            <div class="restaurant-meta"><span>${restaurant.time}</span><span>${restaurant.fee} delivery</span></div>
          </div>
        </a>
        <button class="favorite-button${isFavorite ? " is-favorite" : ""}" type="button" data-favorite="${restaurant.id}" aria-label="${isFavorite ? "Remove" : "Save"} ${restaurant.name} ${isFavorite ? "from" : "to"} favourites" aria-pressed="${isFavorite}">${isFavorite ? "♥" : "♡"}</button>
      </article>`;
  }).join("");
  document.querySelector("#empty-restaurants").classList.toggle("hidden", visibleRestaurants.length > 0);
}

function updateCartCount() {
  const count = [...cart.values()].reduce((total, item) => total + item.quantity, 0);
  cartCount.textContent = String(count);
}

function addToCart(restaurant, menuItem) {
  const key = `${restaurant.id}:${menuItem.name}`;
  const existing = cart.get(key);
  cart.set(key, { ...menuItem, restaurant, quantity: (existing?.quantity ?? 0) + 1 });
  updateCartCount();
  showToast(`${menuItem.name} added to your basket`);
}

function renderRestaurantDetail() {
  const detail = document.querySelector("#restaurant-detail");
  if (!detail || !selectedRestaurant) return;
  detail.innerHTML = `
    <div class="restaurant-detail-heading">
      <div class="restaurant-detail-art image-${selectedRestaurant.art}">${selectedRestaurant.emoji}</div>
      <div><span class="section-kicker">${selectedRestaurant.badge}</span><h1 id="restaurant-detail-title" tabindex="-1">${selectedRestaurant.name}</h1><p>${selectedRestaurant.cuisine}</p><div class="detail-meta"><span>★ ${selectedRestaurant.rating}</span><span>${selectedRestaurant.time}</span><span>${selectedRestaurant.fee} delivery</span></div></div>
    </div>
    <p class="demo-note">Sample menu for the static project preview.</p>
    <h2 class="menu-title">A little something for you</h2>
    <div class="menu-list">${selectedRestaurant.menu.map((item, index) => `
      <article class="menu-item"><div class="menu-item-copy"><h3>${item.name}</h3><p>${item.description}</p></div><span class="menu-item-price">${formatPrice(item.price)}</span><button class="add-button" type="button" data-add-item="${index}" aria-label="Add ${item.name} to basket">+</button></article>`).join("")}</div>`;
}

async function openRestaurant(restaurantId) {
  selectedRestaurant = restaurants.find((restaurant) => restaurant.id === Number(restaurantId));
  if (!selectedRestaurant) return;
  await navigate("restaurant", true);
  document.querySelector("#restaurant-detail-title")?.focus({ preventScroll: true });
}

function renderCart() {
  const content = document.querySelector("#cart-content");
  if (!content) return;
  const items = [...cart.entries()];
  if (items.length === 0) {
    content.innerHTML = `<div class="cart-empty"><span>🧺</span><h2>Your basket's looking a little empty</h2><p>Let's find something delicious around the corner.</p><button class="primary-button" type="button" data-page-link="home">Explore restaurants</button></div>`;
    return;
  }
  const total = items.reduce((sum, [, item]) => sum + item.price * item.quantity, 0);
  content.innerHTML = `
    <div class="cart-list">${items.map(([key, item]) => `
      <div class="cart-row"><div class="cart-row-copy"><strong>${item.name}</strong><small>${item.restaurant.name} · ${formatPrice(item.price)} each</small></div>
      <div class="quantity-control"><button type="button" data-quantity="-1" data-key="${key}" aria-label="Remove one ${item.name}">−</button><span>${item.quantity}</span><button type="button" data-quantity="1" data-key="${key}" aria-label="Add one ${item.name}">+</button></div>
      <strong>${formatPrice(item.price * item.quantity)}</strong></div>`).join("")}</div>
    <div class="cart-summary"><span>Subtotal</span><span>${formatPrice(total)}</span></div>
    <button class="primary-button checkout-button" type="button" id="checkout-button">Continue to checkout <span>→</span></button>
    <p class="demo-note">Checkout is a visual preview. No order will be placed.</p>`;
}

async function navigate(requestedPage, pushHistory = false) {
  const page = pageSections.has(requestedPage) ? requestedPage : "error_404";
  const sequence = ++navigationSequence;
  if (page !== requestedPage) {
    window.history.replaceState({ page }, "", `#${page}`);
  } else if (pushHistory && window.location.hash !== `#${page}`) {
    window.history.pushState({ page }, "", `#${page}`);
  }

  await loadPageView(page);
  if (sequence !== navigationSequence) return;

  document.querySelectorAll(".page").forEach((section) => section.classList.remove("active"));
  const activeSection = pageSections.get(page);
  activeSection.classList.add("active");
  activePage = page;

  document.querySelectorAll(".nav-link").forEach((link) => {
    const selected = link.dataset.page === page;
    link.classList.toggle("active", selected);
    if (selected) link.setAttribute("aria-current", "page");
    else link.removeAttribute("aria-current");
  });
  document.querySelector("#main-nav").classList.remove("is-open");
  document.querySelector("#menu-toggle").setAttribute("aria-expanded", "false");
  document.querySelector("#menu-toggle").setAttribute("aria-label", "Open navigation");
  const behavior = window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth";
  window.scrollTo({ top: 0, behavior });

  if (page === "home") {
    renderRestaurants();
    if (!weatherStarted) {
      weatherStarted = true;
      loadWeather();
    }
  } else if (page === "restaurant") {
    renderRestaurantDetail();
  } else if (page === "cart") {
    renderCart();
  } else if (page === "auth") {
    setAuthMode(authMode);
  }
}

function setAuthMode(mode) {
  authMode = mode;
  const title = document.querySelector("#auth-title");
  const form = document.querySelector("#auth-form");
  if (!title || !form) return;

  document.querySelectorAll("[data-auth-mode]").forEach((button) => button.classList.toggle("selected", button.dataset.authMode === mode));
  title.textContent = mode === "login" ? "Welcome back." : "Come on in.";
  document.querySelector(".auth-intro").textContent = mode === "login"
    ? "Sign in to get a little closer to your local favourites."
    : "Create an account and keep your neighbourhood favourites close.";
  form.querySelector("button").innerHTML = mode === "login" ? 'Sign in <span>→</span>' : 'Create account <span>→</span>';
  document.querySelector("#auth-password").autocomplete = mode === "login" ? "current-password" : "new-password";
  const nameField = document.querySelector(".register-name-field");
  const nameInput = document.querySelector("#auth-name");
  nameField.classList.toggle("hidden", mode !== "register");
  nameInput.required = mode === "register";
}

function selectAdminView(view, moveFocus = false) {
  const tabs = [...document.querySelectorAll(".admin-tabs [role='tab']")];
  tabs.forEach((tab) => {
    const selected = tab.getAttribute("data-admin-view") === view;
    tab.classList.toggle("selected", selected);
    tab.setAttribute("aria-selected", String(selected));
    tab.tabIndex = selected ? 0 : -1;
    if (selected && moveFocus) tab.focus();
  });
  document.querySelectorAll("[data-admin-panel]").forEach((panel) => {
    panel.classList.toggle("active", panel.getAttribute("data-admin-panel") === view);
  });
}

document.addEventListener("click", (event) => {
  const target = event.target;
  if (!(target instanceof Element)) return;

  const retryView = target.closest("[data-retry-view]");
  if (retryView) {
    const page = retryView.getAttribute("data-retry-view");
    if (page) navigate(page);
    return;
  }

  const pageLink = target.closest("[data-page-link]");
  if (pageLink) {
    event.preventDefault();
    navigate(pageLink.getAttribute("data-page-link"), true);
    return;
  }

  const navLink = target.closest("[data-page]");
  if (navLink) {
    event.preventDefault();
    navigate(navLink.getAttribute("data-page"), true);
    return;
  }

  const restaurantLink = target.closest("a[data-restaurant]");
  if (restaurantLink) {
    event.preventDefault();
    openRestaurant(restaurantLink.getAttribute("data-restaurant"));
    return;
  }

  const hashLink = target.closest('a[href^="#"]');
  if (hashLink) {
    event.preventDefault();
    navigate(hashLink.getAttribute("href").slice(1), true);
    return;
  }

  const adminView = target.closest("[data-admin-view]");
  if (adminView) {
    selectAdminView(adminView.getAttribute("data-admin-view"));
    return;
  }

  const adminAction = target.closest("[data-admin-action]");
  if (adminAction) {
    const action = adminAction.getAttribute("data-admin-action");
    const entity = adminAction.getAttribute("data-entity");
    const actionLabel = action === "add" ? "Add" : action === "edit" ? "Edit" : "Delete";
    showToast(`${actionLabel} ${entity} is a static preview; no changes are saved`);
    return;
  }

  const category = target.closest("[data-category]");
  if (category) {
    selectedCategory = category.dataset.category ?? "All";
    document.querySelectorAll(".category-chip").forEach((button) => button.classList.toggle("selected", button === category));
    renderRestaurants();
    return;
  }

  const favorite = target.closest("[data-favorite]");
  if (favorite) {
    const restaurantId = Number(favorite.getAttribute("data-favorite"));
    const isFavorite = !favoriteRestaurantIds.has(restaurantId);
    if (isFavorite) favoriteRestaurantIds.add(restaurantId);
    else favoriteRestaurantIds.delete(restaurantId);
    const restaurant = restaurants.find((item) => item.id === restaurantId);
    favorite.classList.toggle("is-favorite", isFavorite);
    favorite.setAttribute("aria-pressed", String(isFavorite));
    if (restaurant) {
      favorite.setAttribute("aria-label", `${isFavorite ? "Remove" : "Save"} ${restaurant.name} ${isFavorite ? "from" : "to"} favourites`);
    }
    favorite.textContent = isFavorite ? "♥" : "♡";
    showToast(isFavorite ? "Saved to your favourites" : "Removed from your favourites");
    return;
  }

  const addButton = target.closest("[data-add-item]");
  if (addButton) {
    const item = selectedRestaurant.menu[Number(addButton.getAttribute("data-add-item"))];
    if (item) addToCart(selectedRestaurant, item);
    return;
  }

  const quantityButton = target.closest("[data-quantity]");
  if (quantityButton) {
    const key = quantityButton.getAttribute("data-key");
    const item = key ? cart.get(key) : null;
    if (key && item) {
      item.quantity += Number(quantityButton.getAttribute("data-quantity"));
      if (item.quantity <= 0) cart.delete(key);
      updateCartCount();
      renderCart();
    }
    return;
  }

  if (target.closest("#checkout-button")) {
    showToast("Checkout will be available in a future milestone");
    return;
  }

  if (target.closest("#clear-category, #show-all")) {
    selectedCategory = "All";
    searchTerm = "";
    document.querySelector("#restaurant-search").value = "";
    document.querySelectorAll(".category-chip").forEach((chip) => chip.classList.toggle("selected", chip.dataset.category === "All"));
    renderRestaurants();
    return;
  }

  if (target.closest("#menu-toggle")) {
    const button = document.querySelector("#menu-toggle");
    const isOpen = document.querySelector("#main-nav").classList.toggle("is-open");
    button.setAttribute("aria-expanded", String(isOpen));
    button.setAttribute("aria-label", isOpen ? "Close navigation" : "Open navigation");
    return;
  }

  if (target.closest(".location-button")) {
    showToast("Delivery is currently set to Sarajevo");
    return;
  }

  const authModeButton = target.closest("[data-auth-mode]");
  if (authModeButton) {
    setAuthMode(authModeButton.dataset.authMode);
    return;
  }

  if (target.closest("#weather-retry")) loadWeather();
});

document.addEventListener("submit", (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) return;

  if (form.id === "search-form") {
    event.preventDefault();
    searchTerm = document.querySelector("#restaurant-search").value.trim().toLowerCase();
    selectedCategory = "All";
    document.querySelectorAll(".category-chip").forEach((chip) => chip.classList.toggle("selected", chip.dataset.category === "All"));
    renderRestaurants();
    const behavior = window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth";
    document.querySelector("#restaurant-title")?.scrollIntoView({ behavior, block: "center" });
    return;
  }

  if (form.id === "auth-form") {
    event.preventDefault();
    showToast(authMode === "login" ? "Sign-in will be available in a future milestone" : "Account creation will be available in a future milestone");
  }
});

document.addEventListener("keydown", (event) => {
  if (!(event.target instanceof Element)) return;
  const currentTab = event.target.closest(".admin-tabs [role='tab']");
  if (!currentTab) return;

  const tabs = [...document.querySelectorAll(".admin-tabs [role='tab']")];
  const currentIndex = tabs.indexOf(currentTab);
  let nextIndex;

  switch (event.key) {
    case "ArrowRight":
      nextIndex = (currentIndex + 1) % tabs.length;
      break;
    case "ArrowLeft":
      nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
      break;
    case "Home":
      nextIndex = 0;
      break;
    case "End":
      nextIndex = tabs.length - 1;
      break;
    default:
      return;
  }

  event.preventDefault();
  selectAdminView(tabs[nextIndex].getAttribute("data-admin-view"), true);
});

function navigateToCurrentHash() {
  const page = window.location.hash.slice(1) || "home";
  if (page !== activePage) navigate(page);
}

window.addEventListener("popstate", navigateToCurrentHash);
window.addEventListener("hashchange", navigateToCurrentHash);

const initialPage = window.location.hash.slice(1) || "home";
if (!window.location.hash) window.history.replaceState({ page: initialPage }, "", `#${initialPage}`);
navigate(initialPage);
