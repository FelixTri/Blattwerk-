const API = '../../Backend/logic/requestHandler.php';


// Kundenverwaltung für Admins
document.addEventListener('DOMContentLoaded', () => {
  const customerTbody = document.querySelector('#customer-table tbody');
  const ordersSection = document.getElementById('order-details');
  const ordersTbody   = document.querySelector('#orders-table tbody');
  const custNameSpan  = document.getElementById('cust-name');

  async function loadCustomers() {
    customerTbody.innerHTML = '';
    try {
      const res = await fetch(`${API}?action=getCustomers`);
      const customers = await res.json();

      customers.forEach(c => {
        const isActive = c.active == 1;
        const statusTxt = isActive ? 'Aktiv' : 'Inaktiv';
        const btnClass  = isActive ? 'btn-danger' : 'btn-success';
        const btnTxt    = isActive ? 'Deaktivieren' : 'Aktivieren';

        customerTbody.insertAdjacentHTML('beforeend', `
          <tr data-id="${c.id}" data-role="${c.role}">
            <td>${c.id}</td>
            <td>${c.name}</td>
            <td>${c.email}</td>
            <td>${statusTxt}</td>
            <td>
              <button class="btn btn-sm btn-primary view-orders"
                      data-id="${c.id}" data-name="${c.name}">Details</button>
              <button class="btn btn-sm ${btnClass} toggle-active"
                      data-id="${c.id}" data-role="${c.role}" data-active="${c.active}">
                ${btnTxt}
              </button>
            </td>
          </tr>
        `);
      });

      bindDetailButtons();
      bindToggleButtons();
    } catch (err) {
      console.error('Fehler beim Laden der Kunden:', err);
    }
  }

  function bindDetailButtons() {
    document.querySelectorAll('.view-orders').forEach(btn => {
      btn.onclick = async () => {
        const userId = btn.dataset.id;
        custNameSpan.textContent = btn.dataset.name;

        const resp = await fetch(
          `${API}?action=getCustomerOrders&userId=${userId}`
        );
        const orders = await resp.json();
        ordersTbody.innerHTML = '';
        orders.forEach(o => {
          ordersTbody.insertAdjacentHTML('beforeend', `
            <tr data-order-id="${o.order_id}">
              <td>${o.order_id}</td>
              <td>${o.date}</td>
              <td>${parseFloat(o.total).toFixed(2)}</td>
              <td>
                <button class="btn btn-sm btn-secondary view-items"
                        data-order-id="${o.order_id}">
                  Artikel
                </button>
              </td>
            </tr>
          `);
        });
        bindItemButtons();
        ordersSection.style.display = 'block';
      };
    });
  }

  function bindToggleButtons() {
    document.querySelectorAll('.toggle-active').forEach(btn => {
      btn.onclick = async () => {
        const userId = btn.dataset.id;
        const role   = btn.dataset.role;
        const curr   = parseInt(btn.dataset.active, 10);
        const newAct = curr === 1 ? 0 : 1;

        if (role === 'admin') {
          const sure = confirm(
            `Achtung: Sie schalten einen Admin ${
              newAct ? 'AKTIV' : 'INAKTIV'
            }. Sind Sie sicher?`
          );
          if (!sure) return;
        }

        const fd = new FormData();
        fd.append('userId', userId);
        fd.append('active', newAct);
        const resp = await fetch(`${API}?action=toggleUserActive`, {
          method: 'POST', body: fd
        });
        const json = await resp.json();
        if (json.success) {
          loadCustomers();  
        } else {
          alert('Fehler: ' + (json.error || 'Status konnte nicht geändert werden'));
        }
      };
    });
  }

  function bindItemButtons() {
    document.querySelectorAll('.view-items').forEach(btn => {
      btn.onclick = async () => {
        const orderId = btn.dataset.orderId;
        const cls     = `items-for-${orderId}`;
        const exist   = document.querySelector(`.${cls}`);
        if (exist) {
          exist.remove();
          return;
        }
        const resp = await fetch(`${API}?action=getOrderItems&orderId=${orderId}`);
        const items = await resp.json();
        const rows = items.map(it => `
          <tr><td>${it.product_name}</td>
              <td>${it.quantity}</td>
              <td>${parseFloat(it.unit_price).toFixed(2)}</td>
          </tr>
        `).join('');
        const sub = `
          <tr class="${cls}">
            <td colspan="5" class="p-0">
              <table class="table table-sm mb-4">
                <thead><tr>
                  <th>Produkt</th><th>Anzahl</th><th>Einzelpreis (€)</th>
                </tr></thead>
                <tbody>${rows}</tbody>
              </table>
            </td>
          </tr>`;
        document
          .querySelector(`tr[data-order-id="${orderId}"]`)
          .insertAdjacentHTML('afterend', sub);
      };
    });
  }

  loadCustomers();
  loadCustomers();

  // Gutscheinformular-Logik
  const voucherForm = document.getElementById('voucherForm');
  const voucherResult = document.getElementById('voucherResult');

  if (voucherForm) {
    voucherForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      const amount = document.getElementById('amount').value;

      try {
        const response = await fetch('/Blattwerk/Blattwerk-/Backend/logic/saveVoucher.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `amount=${encodeURIComponent(amount)}`
        });

        const data = await response.json();

        if (data.success) {
          voucherResult.innerHTML = `<p style="color: green;">Gutschein erfolgreich erstellt! Code: <strong>${data.code}</strong></p>`;
          voucherForm.reset();
        } else {
          voucherResult.innerHTML = `<p style="color: red;">Fehler: ${data.message}</p>`;
        }
      } catch (error) {
        voucherResult.innerHTML = `<p style="color: red;">Verbindungsfehler.</p>`;
      }
    });
  }

});

