document.addEventListener('DOMContentLoaded', () => {
  const productList     = document.getElementById('product-list');
  const createBtn       = document.getElementById('create-product');
  const formPlaceholder = document.getElementById('form-placeholder');
  const form            = document.getElementById('product-form');
  const categorySelect  = form.querySelector('select[name="category_id"]');

  
  async function loadCategories() {
    const res  = await fetch('../../Backend/logic/requestHandler.php?action=getCategories');
    const cats = await res.json();
    categorySelect.innerHTML = '<option value="">Bitte wählen</option>';
    cats.forEach(c => {
      const opt = document.createElement('option');
      opt.value       = c.id;
      opt.textContent = c.name;
      categorySelect.appendChild(opt);
    });
  }


  async function loadProducts() {
    const res      = await fetch('../../Backend/logic/requestHandler.php?action=getProducts');
    if (!res.ok) {
      console.error('Fehler beim Laden der Produkte');
      return;
    }
    const products = await res.json();
    productList.innerHTML = '';
    products.forEach(p => {
      const div = document.createElement('div');
      div.className = 'card mb-2 p-3';
      div.innerHTML = `
        <h5>${p.name}</h5>
        <p>${p.description}</p>
        <p><strong>${parseFloat(p.price).toFixed(2)} €</strong> – <em>${p.category_name || ''}</em></p>
        <button class="btn btn-sm btn-outline-secondary edit me-2" data-id="${p.id}">Bearbeiten</button>
        <button class="btn btn-sm btn-outline-danger delete" data-id="${p.id}">Löschen</button>
      `;
      productList.appendChild(div);
    });
  }


  loadCategories();
  loadProducts();


  createBtn.onclick = () => {
    form.reset();
    form.id.value = '';
    loadCategories();           
    formPlaceholder.style.display = 'block';
  };


  productList.onclick = async e => {
    const id = e.target.dataset.id;
    if (!id) return;

    if (e.target.classList.contains('edit')) {
      await loadCategories();
      const res  = await fetch(`../../Backend/logic/requestHandler.php?action=getProduct&id=${id}`);
      const prod = await res.json();

      form.id.value          = prod.id;
      form.name.value        = prod.name;
      form.description.value = prod.description;
      form.price.value       = prod.price;
      categorySelect.value   = prod.category_id;

      formPlaceholder.style.display = 'block';
    }

    if (e.target.classList.contains('delete')) {
      if (confirm('Produkt wirklich löschen?')) {
        try {
          const res  = await fetch(`../../Backend/logic/requestHandler.php?action=deleteProduct&id=${id}`);
          const json = await res.json();
          if (json.success) {
            loadProducts();
          } else {
            console.error('Löschen fehlgeschlagen:', json);
            alert('Löschen fehlgeschlagen: ' + (json.error || 'Unbekannter Fehler'));
          }
        } catch (err) {
          console.error('Fetch-Fehler beim Löschen:', err);
          alert('Fehler beim Löschen des Produkts');
        }
      }
    }
  };

  form.onsubmit = async e => {
    e.preventDefault();
    const fd     = new FormData(form);
    const action = form.id.value ? 'updateProduct' : 'createProduct';

    await fetch(`../../Backend/logic/requestHandler.php?action=${action}`, {
      method: 'POST',
      body: fd
    });

    formPlaceholder.style.display = 'none';
    loadProducts();
  };
  
});
