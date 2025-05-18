document.addEventListener('DOMContentLoaded', function () {
  async function loadVouchers() {
    const voucherTableBody = document.querySelector('#voucher-table tbody');
    voucherTableBody.innerHTML = '';

    try {
      const res = await fetch('../../Backend/logic/getVouchers.php'); 
      const text = await res.text();
      console.log("Antwort von getVouchers.php:", text);
      const vouchers = JSON.parse(text);

      vouchers.forEach(voucher => {
        const statusText = voucher.is_active == 1 ? 'Aktiv' : 'Inaktiv';
        const toggleBtnText = voucher.is_active == 1 ? 'Deaktivieren' : 'Aktivieren';

        voucherTableBody.insertAdjacentHTML('beforeend', `
          <tr data-id="${voucher.id}">
            <td>${voucher.id}</td>
            <td>${voucher.code}</td>
            <td>${parseFloat(voucher.amount).toFixed(2)} €</td>
            <td>${statusText}</td>
            <td>${voucher.created_at}</td>
            <td>
              <button class="btn btn-sm btn-primary toggle-voucher">${toggleBtnText}</button>
            </td>
          </tr>
        `);
      });

      bindToggleVoucherButtons();
    } catch (err) {
      console.error('Fehler beim Laden der Gutscheine:', err);
    }
  }

  function bindToggleVoucherButtons() {
    document.querySelectorAll('.toggle-voucher').forEach(btn => {
      btn.onclick = async function () {
        const tr = btn.closest('tr');
        const voucherId = tr.dataset.id;

        try {
          const response = await fetch('../../Backend/logic/toggleVoucher.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${voucherId}`
          });

          const data = await response.json();

          if (data.success) {
            loadVouchers();
          } else {
            alert('Fehler: ' + data.message);
          }
        } catch (err) {
          console.error('Fehler beim Aktualisieren des Gutscheinstatus:', err);
        }
      };
    });
  }

  document.querySelector('#voucher-create-btn')?.addEventListener('click', async () => {
    const amountInput = document.querySelector('#voucher-amount');
    const amount = parseFloat(amountInput.value);

    if (isNaN(amount) || amount <= 0) {
      alert('Bitte gültigen Betrag eingeben.');
      return;
    }

    try {
      const res = await fetch('../../Backend/logic/saveVoucher.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `amount=${amount}`
      });

      const text = await res.text();
      console.log("Antwort von saveVoucher.php:", text);

      const data = JSON.parse(text);

      if (data.success) {
        alert('Gutschein erstellt: ' + data.code);
        loadVouchers();
      } else {
        alert('Fehler: ' + data.message);
      }
    } catch (err) {
      console.error('Fehler beim Erstellen:', err);
    }
  });

  loadVouchers();
});
