// Accountdaten anzeigen und über Modal speichern

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("editAccountForm");
  let pendingFormData = null; // Zwischenspeicher für Formulardaten

  // Accountdaten beim Laden anzeigen
  fetch("../../Backend/logic/requestHandler.php?action=getSessionInfo", { credentials: 'include' })
    .then(res => res.json())
    .then(user => {
      if (!user.user_id) {
        alert("Bitte melde dich zuerst an.");
        window.location.href = "../sites/login.html";
        return;
      }

      ['salutation', 'address', 'postal_code', 'city', 'email', 'username', 'payment_info'].forEach(field => {
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

  // Beim Absenden: Statt direkt zu speichern Passwortmodal öffnen
  form.addEventListener("submit", e => {
    e.preventDefault();

    const formData = new FormData(form);
    pendingFormData = Object.fromEntries(formData.entries());

    // Modal anzeigen
    const modal = new bootstrap.Modal(document.getElementById("passwordModal"));
    modal.show();
  });

  // Passwort im Modal eingeben → Daten speichern
  document.getElementById("password-form").addEventListener("submit", async e => {
    e.preventDefault();
    const password = document.getElementById("password-input").value;

    if (!password) {
      alert("Bitte gib dein Passwort ein.");
      return;
    }

    if (!pendingFormData) return;

    // Passwort an Payload anhängen
    pendingFormData.password = password;

    try {
      const res = await fetch("../../Backend/logic/editAccount.php", {
        method: "POST",
        credentials: 'include',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(pendingFormData)
      });

      const result = await res.json();
      if (result.success) {
        alert("Accountdaten erfolgreich gespeichert!");
        location.reload();
      } else {
        alert(result.message || "Fehler beim Speichern.");
      }
    } catch (err) {
      console.error("Fehler beim Speichern:", err);
      alert("Ein technischer Fehler ist aufgetreten.");
    }

    // Modal zurücksetzen
    bootstrap.Modal.getInstance(document.getElementById("passwordModal")).hide();
    document.getElementById("password-input").value = "";
  });
});