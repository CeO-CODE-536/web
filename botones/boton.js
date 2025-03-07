function adjustView() {
    const app = document.getElementById("app");
    const width = window.innerWidth;
  
    if (width < 600) {
      app.innerHTML = `
        <h1>Bienvenido a Wear OS</h1>
        <p class="wear-os">Esta es la vista para dispositivos pequeños como Wear OS.</p>
      `;
    } else if (width < 1200) {
      app.innerHTML = `
        <h1>Bienvenido a la App</h1>
        <p class="tablet">Esta es la vista para tablets.</p>
      `;
    } else {
      app.innerHTML = `
        <h1>Bienvenido a la App</h1>
        <p class="desktop">Esta es la vista para dispositivos de escritorio.</p>
      `;
    }
  }
  