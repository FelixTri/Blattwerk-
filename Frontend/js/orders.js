document.addEventListener('DOMContentLoaded', () => {
    fetchOrders();
  
    async function fetchOrders() {
      try {
        const res = await fetch('../../Backend/logic/getOrders.php', { credentials: 'include' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        renderOrders(data.orders);
      } catch (err) {
        document.getElementById('orders-list').innerHTML =
          `<div class="alert alert-danger">Fehler: ${err.message}</div>`;
      }
    }
  
    function renderOrders(orders) {
      const list = document.getElementById('orders-list');
      if (orders.length === 0) {
        list.innerHTML = '<p class="text-muted">Keine Bestellungen vorhanden.</p>';
        return;
      }
  
      // Bootstrap list-group
      let html = '<ul class="list-group">';
      orders.forEach(o => {
        const date = new Date(o.created_at).toLocaleDateString('de-DE');
        html += `
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              Bestellung #${o.id} <small class="text-muted">vom ${date}</small>
            </div>
            <div class="btn-group btn-group-sm" role="group" aria-label="Aktionen">
              <button 
                class="btn btn-outline-primary" 
                data-id="${o.id}" 
                onclick="loadDetails(${o.id})"
              >
                Details
              </button>
              <button 
                class="btn btn-success" 
                data-id="${o.id}" 
                onclick="window.open('../../Backend/logic/printInvoice.php?orderId=${o.id}','_blank')"
              >
                Rechnung
              </button>
            </div>
          </li>`;
      });
      html += '</ul>';
      list.innerHTML = html;
    }
  
    // Expose these functions globally so inline onclicks can call them
    window.loadDetails = async function(orderId) {
      try {
        const res = await fetch(
          `../../Backend/logic/getOrderDetails.php?orderId=${orderId}`,
          { credentials: 'include' }
        );
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showDetails(data.order);
      } catch (err) {
        alert('Fehler beim Laden: ' + err.message);
      }
    };
  
    function showDetails(order) {
      let html = `
        <h2 class="mt-4">
          Bestellung #${order.id} 
          <small class="text-muted">vom ${new Date(order.created_at).toLocaleDateString('de-DE')}</small>
        </h2>
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>Artikel</th>
                <th>Menge</th>
                <th>Preis</th>
              </tr>
            </thead>
            <tbody>
      `;
      order.items.forEach(item => {
        html += `
          <tr>
            <td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>${parseFloat(item.price).toFixed(2)} €</td>
          </tr>`;
      });
      html += `
            </tbody>
          </table>
        </div>
      `;
      const det = document.getElementById('order-details');
      det.innerHTML = html;
      det.style.display = 'block';
    }
  });