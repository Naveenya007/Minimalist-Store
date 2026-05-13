(function ($) {
  const currency = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
  const cartKey = 'minimalist-store-cart';

  function readCart() {
    try {
      return JSON.parse(localStorage.getItem(cartKey) || '[]');
    } catch (error) {
      return [];
    }
  }

  function writeCart(cart) {
    localStorage.setItem(cartKey, JSON.stringify(cart));
    updateCartBadge();
  }

  function addToCart(product) {
    const cart = readCart();
    const existing = cart.find((item) => item.id === product.id);

    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({ ...product, quantity: 1 });
    }

    writeCart(cart);
  }

  function removeFromCart(productId) {
    const updated = readCart().filter((item) => String(item.id) !== String(productId));
    writeCart(updated);
    renderCartPage();
  }

  function updateCartBadge() {
    const count = readCart().reduce((sum, item) => sum + item.quantity, 0);
    $('[data-cart-count]').text(count);
  }

  function toProductButtonData(product) {
    return encodeURIComponent(JSON.stringify(product));
  }

  function renderProducts(products) {
    const grid = $('#product-grid');
    if (!grid.length) {
      return;
    }

    grid.empty();

    products.forEach((product) => {
      const card = $('<article class="card product-card"></article>');
      card.append(`<img src="${product.image}" alt="${product.name}">`);
      card.append(`<h3>${product.name}</h3>`);
      card.append(`<p>${product.description}</p>`);
      card.append(`<p class="price">${currency.format(product.price)}</p>`);
      card.append(`<button type="button" class="add-to-cart" data-product="${toProductButtonData(product)}">Add to Cart</button>`);
      grid.append(card);
    });
  }

  function renderFeatured(products) {
    const featuredContainer = $('#featured-product');
    if (!featuredContainer.length) {
      return;
    }

    const featured = products.find((product) => Number(product.featured) === 1) || products[0];
    if (!featured) {
      return;
    }

    featuredContainer.html(`
      <img src="${featured.image}" alt="${featured.name}">
      <h3>${featured.name}</h3>
      <p>${featured.description}</p>
      <p class="price">${currency.format(featured.price)}</p>
      <button type="button" class="add-to-cart" data-product="${toProductButtonData(featured)}">Add to Cart</button>
    `);
  }

  function renderCartPage() {
    const container = $('#cart-items');
    if (!container.length) {
      return;
    }

    const cart = readCart();
    const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    if (!cart.length) {
      container.html('<p class="empty-state">Your cart is empty. Browse the products page to add something simple.</p>');
      $('#total').text('Total: ' + currency.format(0));
      return;
    }

    const list = $('<div class="cart-list"></div>');
    cart.forEach((item) => {
      const row = $(
        `<div class="cart-row">
          <img src="${item.image}" alt="${item.name}">
          <div>
            <h3>${item.name}</h3>
            <p>${currency.format(item.price)} x ${item.quantity}</p>
          </div>
          <button type="button" class="remove-from-cart" data-id="${item.id}">Remove</button>
        </div>`
      );
      list.append(row);
    });

    container.html(list);
    $('#total').text('Total: ' + currency.format(total));
    displayUserInfo();
  }

  function submitOrder() {
    const form = $('#order-form');
    if (!form.length) {
      return;
    }

    form.on('submit', function (event) {
      event.preventDefault();

      const cart = readCart();
      const payload = {
        items: cart,
        total: cart.reduce((sum, item) => sum + item.price * item.quantity, 0),
      };

      $.ajax({
        url: 'api/order.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
      })
        .done((response) => {
          $('#order-status').html('<p class="success">✓ ' + response.message + '</p>');
          writeCart([]);
          renderCartPage();
          form[0].reset();
        })
        .fail((xhr) => {
          const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to save the order right now.';
          if (xhr.status === 401) {
            $('#order-status').html('<p class="error">✗ Please log in first to place an order</p>');
          } else {
            $('#order-status').html('<p class="error">✗ ' + message + '</p>');
          }
        });
    });
  }

  function displayUserInfo() {
    $.ajax({
      url: 'api/session.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.loggedIn && response.user) {
          $('#user-info').text('Placing order as: ' + response.user.name + ' (' + response.user.email + ')');
        }
      }
    });
  }

  function submitContact() {
    const form = $('#contact-form');
    if (!form.length) {
      return;
    }

    form.on('submit', function (event) {
      event.preventDefault();

      $.ajax({
        url: 'api/contact.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
          name: $('#contact-name').val(),
          email: $('#contact-email').val(),
          message: $('#contact-message').val(),
        }),
      })
        .done((response) => {
          $('#contact-status').text(response.message).addClass('success');
          form[0].reset();
        })
        .fail((xhr) => {
          const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to save the message right now.';
          $('#contact-status').text(message).removeClass('success');
        });
    });
  }

  function bindCartActions() {
    $(document).on('click', '.add-to-cart', function () {
      const encoded = $(this).data('product');
      const product = JSON.parse(decodeURIComponent(encoded));
      addToCart(product);
    });

    $(document).on('click', '.remove-from-cart', function () {
      removeFromCart($(this).data('id'));
    });
  }

  function loadProducts() {
    if (!$('#product-grid').length && !$('#featured-product').length) {
      updateCartBadge();
      renderCartPage();
      submitOrder();
      submitContact();
      return;
    }

    $.getJSON('api/products.php').done((response) => {
      const products = response.products || [];
      renderProducts(products);
      renderFeatured(products);
      updateCartBadge();
      renderCartPage();
      submitOrder();
      submitContact();
    });
  }

  function checkSession() {
    $.ajax({
      url: 'api/session.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.loggedIn && response.user) {
          $('#user-menu').show();
          $('#auth-menu').hide();
        } else {
          $('#user-menu').hide();
          $('#auth-menu').show();
        }
      }
    });
  }

  $(function () {
    checkSession();
    bindCartActions();
    loadProducts();
  });
})(jQuery);
