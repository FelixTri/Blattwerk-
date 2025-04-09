// register.js

document.addEventListener("DOMContentLoaded", () => {
    const registrationForm = document.getElementById('registrationForm');
    const passwordError = document.getElementById('passwordError');
  
    registrationForm.addEventListener('submit', (event) => {
      event.preventDefault();
      passwordError.textContent = '';
  
      // Passwörter vergleichen
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      if (password !== confirmPassword) {
        passwordError.textContent = 'Die Passwörter stimmen nicht überein!';
        return;
      }
  
      // Formulardaten zusammenstellen
      const formData = {
        salutation: document.getElementById('salutation').value,
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        address: document.getElementById('address').value,
        postalCode: document.getElementById('postalCode').value,
        city: document.getElementById('city').value,
        email: document.getElementById('email').value,
        username: document.getElementById('username').value,
        password: password,
        paymentInfo: document.getElementById('paymentInfo').value,
        role: document.getElementById('role').value,
        active: document.getElementById('active').value
      };
  
      // Der korrekte relative Pfad: 2 Ebenen nach oben, dann Backend/logic/
      fetch('../../Backend/logic/registerUser.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      })
      .then(response => {
        if (!response.ok) {
          return response.text().then(text => { throw new Error(text) });
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          alert('Registrierung erfolgreich! Bitte melde dich jetzt an.');
          // Optional: Weiterleitung
          // window.location.href = '../sites/login.html';
        } else {
          alert('Registrierung fehlgeschlagen: ' + (data.message || 'Unbekannter Fehler'));
        }
      })
      .catch(error => {
        console.error('Fehler bei der Registrierung:', error);
        alert('Ein Fehler ist aufgetreten. Bitte überprüfe die Konsole.');
      });
    });
  });
  