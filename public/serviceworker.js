self.addEventListener('push', (event) => {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    console.warn('Notifications permission not granted.');
    return;
  }

  let data = {};
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      console.error('Push data parse failed:', e);
      return;
    }
  }

  console.log('Push received:', data);

  if (!data.title) {
    console.error('Notification title missing');
    return;
  }

  const options = {
    body: data.body || '',
    icon: '/favicon.ico', // Attempt to use favicon as icon
    requireInteraction: true, // Force notification to stay on screen
    data: data // Pass the entire data object to use in click handler
  };

  event.waitUntil(
    self.registration
      .showNotification(data.title, options)
      .then(() => console.log('Notification shown successfully'))
      .catch((err) => console.error('Error showing notification:', err))
  );
});

self.addEventListener('notificationclick', (event) => {
  console.log('Notification clicked:', event.notification);
  event.notification.close();

  if (event.notification.data && event.notification.data.url) {
    event.waitUntil(clients.openWindow(event.notification.data.url));
  }
});
