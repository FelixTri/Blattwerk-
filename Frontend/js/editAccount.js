// Accountdaten anzeigen und bearbeiten
document.addEventListener("DOMContentLoaded", () => {
  // Felder beim Laden vom Server holen
  fetch("../../Backend/logic/requestHandler.php?action=getSessionInfo", { credentials: 'include' })
    .then(res => res.json())
    .then(user => {
      if (!user.user_id) {
        alert("Bitte melde dich zuerst an.");
        window.location.href = "../sites/login.html";
        return;
      }
      // Formularfelder befüllen
      ['salutation','address','postal_code','city','email','username','payment_info'].forEach(field => {
        const el = document.getElementById(field);
        if (el && user[field] !== undefined) {
          el.value = user[field];
        }
      });
      document.getElementById('username').readOnly = true;
    })
    .catch(err => {
      console.error("Fehler beim Laden der Account-Daten:", err);
    });

  // Submit-Handler
  const form = document.getElementById("editAccountForm");
  form.addEventListener("submit", event => {
    event.preventDefault();

    const payload = {
      salutation:   form.salutation.value,
      address:      form.address.value,
      postal_code:  form.postal_code.value,
      city:         form.city.value,
      email:        form.email.value,
      payment_info: form.payment_info.value
    };

    fetch("../../Backend/logic/editAccount.php", {
      method: "POST",
      credentials: 'include',
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // neuen User ins localStorage schreiben
        localStorage.setItem('user', JSON.stringify(data.user));
        alert("Accountdaten erfolgreich gespeichert!");
      } else {
        alert(data.message || "Speichern fehlgeschlagen.");
      }
    })
    .catch(err => {
      console.error("Technischer Fehler beim Speichern:", err);
      alert("Ein technischer Fehler ist aufgetreten. Bitte versuche es später erneut.");
    });
  });
});