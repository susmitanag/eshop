// Everly front end behaviour. Plain JavaScript, no libraries.
// 1. Toast messages
// 2. Quantity stepper (+ and -)
// 3. Add to cart without reloading the page
// 4. Cart: change quantity or remove an item, totals update straight away
// 5. Shop filters submit on change, and the sort menu

document.addEventListener('DOMContentLoaded', function () {
    initQuantityStepper();
    initAddToCart();
    initCart();
    initFilters();
});

var AJAX_HEADERS = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };

/* ---------- Toast ---------- */

function showToast(message) {
    var region = document.getElementById('toast-region');
    if (!region) return;
    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    region.appendChild(toast);
    setTimeout(function () {
        toast.classList.add('leaving');
        setTimeout(function () { toast.remove(); }, 260);
    }, 2600);
}

function updateCartBadge(count) {
    var badge = document.getElementById('js-cart-badge');
    if (!badge) return;
    badge.textContent = count;
    badge.classList.remove('bump');
    void badge.offsetWidth;
    badge.classList.add('bump');
}

/* ---------- Quantity stepper ---------- */

function initQuantityStepper() {
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.qty-btn');
        if (!button) return;
        var input = button.parentElement.querySelector('input[type="number"]');
        if (!input) return;

        var step = parseInt(button.dataset.step, 10);
        var min = parseInt(input.min, 10) || 1;
        var max = parseInt(input.max, 10) || Infinity;
        var next = (parseInt(input.value, 10) || min) + step;
        next = Math.max(min, Math.min(max, next));

        if (String(next) !== input.value) {
            input.value = next;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
}

/* ---------- Add to cart ---------- */

function initAddToCart() {
    var form = document.getElementById('add-to-cart-form');
    if (!form) return;

    var note = document.getElementById('add-to-cart-message');
    var button = document.getElementById('add-to-cart-btn');

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var original = button.textContent;
        button.disabled = true;
        button.textContent = 'Adding...';

        fetch(form.action, { method: 'POST', headers: AJAX_HEADERS, body: new FormData(form) })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    updateCartBadge(data.cart_count);
                    showToast(data.capped ? 'Added the last ones we have in stock' : 'Added to your cart');
                    if (note) {
                        note.innerHTML = data.cart_qty + ' in your cart. <a href="cart.php">View cart</a>';
                        note.hidden = false;
                    }
                    button.textContent = 'Added';
                    setTimeout(function () { button.textContent = original; }, 1200);
                } else {
                    showToast(data.error || 'Could not add this item.');
                    button.textContent = original;
                }
            })
            .catch(function () { form.submit(); })
            .finally(function () { button.disabled = false; });
    });
}

/* ---------- Cart page ---------- */

function initCart() {
    var rows = document.getElementById('cart-rows');
    if (!rows) return;

    var timers = {};

    function applyTotals(data) {
        setText('js-subtotal', data.subtotal);
        setText('js-shipping', data.shipping);
        setText('js-total', data.total);
        updateCartBadge(data.cart_count);

        var meter = document.getElementById('js-meter');
        if (meter) meter.style.width = data.progress + '%';
        var note = document.getElementById('js-ship-note');
        if (note) {
            note.innerHTML = data.to_free_raw > 0
                ? 'Add <strong>' + data.to_free + '</strong> more for free delivery'
                : 'You get <strong>free delivery</strong> on this order';
        }

        if (data.is_empty) {
            document.getElementById('cart-content').innerHTML =
                '<div class="empty-state" id="cart-empty-message"><h3>Your cart is empty</h3>' +
                '<p>Add something you like and it will show up here.</p>' +
                '<a href="shop.php" class="btn btn-primary">Start shopping</a></div>';
        }
    }

    function setText(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    function send(form, row, removing) {
        fetch(form.action, { method: 'POST', headers: AJAX_HEADERS, body: new FormData(form) })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) return;
                if (removing) {
                    row.style.transition = 'opacity .2s ease, transform .2s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(16px)';
                    setTimeout(function () { row.remove(); }, 200);
                    showToast('Item removed');
                } else {
                    var line = row.querySelector('.js-line-total');
                    if (line) line.textContent = data.line_total;
                    var input = row.querySelector('.js-qty-input');
                    if (input && data.qty) input.value = data.qty;
                }
                applyTotals(data);
            })
            .catch(function () { showToast('Something went wrong. Please try again.'); });
    }

    rows.addEventListener('change', function (event) {
        var input = event.target.closest('.js-qty-input');
        if (!input) return;
        var form = input.closest('.js-update-form');
        var row = input.closest('.cart-row');
        var id = row.dataset.productId;
        clearTimeout(timers[id]);
        timers[id] = setTimeout(function () { send(form, row, false); }, 250);
    });

    rows.addEventListener('submit', function (event) {
        var form = event.target;
        var row = form.closest('.cart-row');
        if (form.classList.contains('js-remove-form')) {
            event.preventDefault();
            send(form, row, true);
        } else if (form.classList.contains('js-update-form')) {
            event.preventDefault();
            send(form, row, false);
        }
    });
}

/* ---------- Shop filters ---------- */

function initFilters() {
    document.querySelectorAll('form[data-autosubmit]').forEach(function (form) {
        form.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(function (field) {
            field.addEventListener('change', function () { form.submit(); });
        });
    });

    document.querySelectorAll('select[data-sort]').forEach(function (select) {
        select.addEventListener('change', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('sort', select.value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    });
}
