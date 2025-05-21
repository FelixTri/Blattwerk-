document.addEventListener("DOMContentLoaded", () => {
    // Session-Daten vom Server holen (inkl. payment_info)
    fetch("../../Backend/logic/requestHandler.php?action=getSessionInfo", {
      credentials: "include"
    })
      .then(res => {
        if (!res.ok) throw new Error("Keine Session-Daten");
        return res.json();
      })
      .then(user => {
        if (!user.user_id) {
          alert("Bitte melde dich zuerst an.");
          window.location.href = "login.html";
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
        console.error("Fehler beim Laden deiner Daten:", err);
        alert("Fehler beim Laden deiner Daten.");
      });
  });