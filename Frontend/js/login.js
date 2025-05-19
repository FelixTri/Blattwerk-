// Login Logik
// leitet eingegebene Daten an den Backend-Login-Handler weiter
document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const loginError = document.getElementById("loginError");

  loginForm.addEventListener("submit", (event) => {
    event.preventDefault();
    loginError.textContent = "";

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const remember = document.getElementById("remember").checked;

    fetch("../../Backend/logic/loginUser.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ email, password, remember })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          localStorage.setItem('user', JSON.stringify(data.user));
          window.location.href = "../index.html";
        } else {
          loginError.textContent = data.message || "Login fehlgeschlagen";
        }
      })
      .catch(err => {
        console.error("Fehler beim Login:", err);
        loginError.textContent = "Ein technischer Fehler ist aufgetreten.";
      });
  });
});