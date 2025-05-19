// Login Logik
// Leitet eingegebene Login-Daten an das Backend weiter und verarbeitet die Antwort

document.addEventListener("DOMContentLoaded", () => {
  const loginForm  = document.getElementById("loginForm");
  const loginError = document.getElementById("loginError");

  // Beim Absenden des Login-Formulars
  loginForm.addEventListener("submit", (event) => {
    event.preventDefault(); 
    loginError.textContent = ""; 

    // Formulardaten auslesen
    const email    = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const remember = document.getElementById("remember").checked;

    // Login-Daten per POST an Backend senden
    fetch("../../Backend/logic/loginUser.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ email, password, remember })
    })
      .then(res => res.json()) // Antwort parsen
      .then(data => {
        if (data.success) {
          // Bei Erfolg Nutzer im localStorage speichern und weiterleiten
          localStorage.setItem('user', JSON.stringify(data.user));
          window.location.href = "../index.html";
        } else {
          // Fehlermeldung anzeigen
          loginError.textContent = data.message || "Login fehlgeschlagen";
        }
      })
      .catch(err => {
        console.error("Fehler beim Login:", err);
        loginError.textContent = "Ein technischer Fehler ist aufgetreten.";
      });
  });
});