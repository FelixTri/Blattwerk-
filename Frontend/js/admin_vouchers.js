// Gutscheinverwaltung im Admin-Bereich

document.addEventListener('DOMContentLoaded', () => {
  const tableBody = document.querySelector('#voucher-table tbody');
  const createBtn = document.querySelector('#voucher-create-btn');
  const amountInput = document.querySelector('#voucher-amount');

  // Gutscheine aus dem Backend laden und in Tabelle anzeigen
  async function loadVouchers() {
    try {
      const res = await fetch('../../Backend/logic/getVouchers.php');
      const vouchers = await res.json();

      tableBody.innerHTML = ''; // Tabelle leeren

      vouchers.forEach(voucher => {
        const statusText = voucher.is_active == 1 ? 'Aktiv' : 'Inaktiv';
        const toggleText = voucher.is_active == 1 ? 'Deaktivieren' : 'Aktivieren';

        const row = document.createElement('tr');
        row.dataset.id = voucher.id;
        row.innerHTML = `
          <td>${voucher.id}</td>
          <td>${voucher.code}</td>
          <td>${parseFloat(voucher.amount).toFixed(2)} €</td>
          <td>${statusText}</td>
          <td>${voucher.created_at}</td>
          <td><button class="btn btn-sm btn-primary toggle-voucher">${toggleText}</button></td>
        `;
        tableBody.appendChild(row);
      });

      bindToggleButtons(); // Nach dem Einfügen Buttons aktivieren
    } catch (err) {
      console.error('Fehler beim Laden der Gutscheine:', err);
    }
  }

  // Gutscheine aktivieren/deaktivieren oder löschen
  function bindToggleButtons() {
    document.querySelectorAll('.toggle-voucher').forEach(button => {
      button.addEventListener('click', async () => {
        const voucherId = button.closest('tr').dataset.id;

        // Bestätigung zum Löschen
        if (!confirm("Diesen Gutschein wirklich löschen?")) return;

        try {
          const res = await fetch('../../Backend/logic/deleteVoucher.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${voucherId}`
          });

          const result = await res.json();
          if (result.success) {
            await loadVouchers(); // Tabelle neu laden nach Löschung
          } else {
            alert('Fehler: ' + result.message);
          }
        } catch (err) {
          console.error('Fehler beim Löschen:', err);
        }
      });
    });
  }

  // Neuen Gutschein erstellen (per Eingabe + Button)
  createBtn?.addEventListener('click', async () => {
    const amount = parseFloat(amountInput.value);
    if (isNaN(amount) || amount <= 0) {
      alert('Bitte gültigen Betrag eingeben.');
      return;
    }

    try {
      const res = await fetch('../../Backend/logic/saveVoucher.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `amount=${amount}`
      });

      const text = await res.text();
      console.log('Antwort von saveVoucher.php:', text);

      let data;
      try {
        data = JSON.parse(text); // Antwort als JSON parsen
      } catch (e) {
        alert('Ungültige Antwort vom Server.');
        return;
      }

      if (data.success) {
        alert('Gutschein erstellt: ' + data.code);
        amountInput.value = '';      // Eingabefeld leeren
        await loadVouchers();        // Tabelle aktualisieren
      } else {
        alert('Fehler: ' + data.message);
      }
    } catch (err) {
      console.error('Fehler beim Erstellen des Gutscheins:', err);
    }
  });

  loadVouchers(); // Direkt beim Laden der Seite Gutscheine anzeigen
});