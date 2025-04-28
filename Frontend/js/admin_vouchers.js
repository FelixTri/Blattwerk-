document.addEventListener('DOMContentLoaded', function() {

    async function loadVouchers() {
      const voucherTableBody = document.querySelector('#voucher-table tbody');
      voucherTableBody.innerHTML = '';
  
      try {
        const res = await fetch('/Blattwerk/Blattwerk-/Backend/logic/getVouchers.php');
        const vouchers = await res.json();
  
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
        btn.onclick = async function() {
          const tr = btn.closest('tr');
          const voucherId = tr.dataset.id;
  
          try {
            const response = await fetch('/Blattwerk/Blattwerk-/Backend/logic/toggleVoucher.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `id=${voucherId}`
            });
  
            const data = await response.json();
  
            if (data.success) {
              loadVouchers(); // Nach Änderung neu laden
            } else {
              alert('Fehler: ' + data.message);
            }
          } catch (err) {
            console.error('Fehler beim Aktualisieren des Gutscheinstatus:', err);
          }
        };
      });
    }
  
    loadVouchers();
  
  });