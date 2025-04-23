// editAccount.js
document.addEventListener("DOMContentLoaded", () => {
    const editAccountForm = document.getElementById("editAccountForm");
  
    editAccountForm.addEventListener("submit", (event) => {
      event.preventDefault();
  
      const storedUser = JSON.parse(localStorage.getItem('user'));
      if (!storedUser) {
        alert("Bitte melde dich erneut an.");
        window.location.href = "../login.html";
        return;
      }
  
      const fields = ["salutation", "address", "postal_code", "city", "email", "payment_info"];
      const formData = {
        username: storedUser.username,
        salutation: document.getElementById('salutation').value,
        address: document.getElementById('address').value,
        postal_code: document.getElementById('postal_code').value,
        city: document.getElementById('city').value,
        email: document.getElementById('email').value,
        payment_info: document.getElementById('payment_info').value
      };
  
      fields.forEach(field => {
        const fieldValue = document.getElementById(field).value;
        if (fieldValue && fieldValue !== storedUser[field]) {
          formData[field] = fieldValue;
        }
      });
  
      fetch("../../Backend/logic/editAccount.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
      })
      .then(response => {
        if (!response.ok) {
          return response.text().then(text => {
            console.error("❌ Serverantwort (nicht OK):", text); 
            throw new Error("Serverantwort war nicht OK");
          });
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          alert("Deine Accountdaten wurden erfolgreich aktualisiert!");
          Object.assign(storedUser, formData);
          localStorage.setItem('user', JSON.stringify(storedUser));
          window.location.href = "../index.html";
        } else {
          console.error("Fehlermeldung vom Server:", data.message);
          alert(data.message || "Aktualisierung fehlgeschlagen.");
        }
      })
      .catch((err) => {
        console.error("❌ Technischer Fehler beim Speichern:", err);
        alert("Ein technischer Fehler ist aufgetreten. Bitte versuche es später erneut.");
      });
    });
  });