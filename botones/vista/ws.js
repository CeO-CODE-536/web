self.addEventListener('push', (event) => {
    const data = event.data.json();
    const options = {
      body: data.body,
      icon: data.icon || 'https://via.placeholder.com/128',
    };
    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  });
  