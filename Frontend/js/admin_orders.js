// Admin-Bestellübersicht
// Zeigt alle Bestellungen im Adminbereich an und erlaubt das Anzeigen und Entfernen von Artikeln aus Bestellungen

document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.querySelector("#admin-order-table tbody");
  
    // Lädt alle Bestellungen aus dem Backend und zeigt sie in der Tabelle an
    async function loadOrders() {
      try {
        const res = await fetch("../../Backend/logic/requestHandler.php?action=getAllOrders");
        const data = await res.json();
  
        tableBody.innerHTML = "";
  
        if (!Array.isArray(data) || data.length === 0) {
          tableBody.innerHTML = "<tr><td colspan='5'>Keine Bestellungen gefunden.</td></tr>";
          return;
        }
  
        console.log("Backend-Antwort:", data); // Debug-Ausgabe
  
        // Jede Bestellung als Tabellenzeile einfügen
        data.forEach(order => {
            const row = document.createElement("tr");
            row.innerHTML = `
            <td>${order.order_id}</td>
            <td>${order.customer_name}</td>
            <td>${parseFloat(order.total).toFixed(2)} €</td>
            <td>${order.date}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary view-products" data-id="${order.order_id}">
                Anzeigen
                </button>
            </td>
            `;
            tableBody.appendChild(row);
        });
  
        bindProductButtons();
      } catch (err) {
        console.error("Fehler beim Laden der Bestellungen:", err);
      }
    }
  
    // Verbindet die „Anzeigen“-Buttons mit dem Anzeigen der Artikel
    function bindProductButtons() {
      document.querySelectorAll(".view-products").forEach(btn => {
        btn.addEventListener("click", async () => {
          const orderId = btn.dataset.id;
          const existingRow = document.querySelector(`.products-row-${orderId}`);
  
          // Wenn bereits sichtbar, Zeile entfernen
          if (existingRow) {
            existingRow.remove();
            return;
          }
  
          try {
            const res = await fetch(`../../Backend/logic/requestHandler.php?action=getOrderItems&orderId=${orderId}`);
            const items = await res.json();
  
            // Neue Zeile unter der Bestellung einfügen
            const subRow = document.createElement("tr");
            subRow.className = `products-row-${orderId}`;
            subRow.innerHTML = `
              <td colspan="5">
                <table class="table table-bordered mb-0">
                  <thead>
                    <tr><th>Produkt</th><th>Menge</th><th>Einzelpreis</th><th>Aktion</th></tr>
                  </thead>
                  <tbody>
                    ${items.map(it => `
                      <tr>
                        <td>${it.product_name}</td>
                        <td>${it.quantity}</td>
                        <td>${parseFloat(it.unit_price).toFixed(2)} €</td>
                        <td><button class="btn btn-sm btn-danger remove-item" data-order-id="${orderId}" data-product-id="${it.product_id}">Entfernen</button></td>
                      </tr>
                    `).join("")}
                  </tbody>
                </table>
              </td>
            `;
            btn.closest("tr").after(subRow);
  
            bindRemoveButtons();
          } catch (err) {
            console.error("Fehler beim Laden der Bestellartikel:", err);
          }
        });
      });
    }
  
    // Entfernt ein Produkt aus einer Bestellung
    function bindRemoveButtons() {
      document.querySelectorAll(".remove-item").forEach(btn => {
        btn.addEventListener("click", async () => {
          const orderId = btn.dataset.orderId;
          const productId = btn.dataset.productId;
  
          if (!confirm("Produkt aus Bestellung entfernen?")) return;
  
          try {
            const res = await fetch("../../Backend/logic/requestHandler.php?action=removeOrderItem", {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: `orderId=${orderId}&productId=${productId}`
            });
            const result = await res.json();
  
            if (result.success) {
              alert("Produkt entfernt.");
              loadOrders(); // Liste aktualisieren
            } else {
              alert("Fehler: " + (result.message || "Unbekannter Fehler beim Entfernen"));
            }
          } catch (err) {
            console.error("Fehler beim Entfernen des Artikels:", err);
          }
        });
      });
    }
  
    // Initialer Ladevorgang
    loadOrders();
  });