/**
 * Google Reviews configuration
 *
 * OPTION A (GitHub Pages / static hosting):
 *   Add your Google Maps API key below (Places API New enabled).
 *
 * OPTION B (PHP hosting):
 *   Copy api/config.example.php to api/config.php and add your API key there.
 *   Leave apiKey empty below — reviews load from api/google-reviews.php
 */
window.GREEN_TORQUE_REVIEWS = {
  apiKey: '',
  /** Optional: free widget from https://www.trustindex.io/ — connects to Google Business, no API key needed */
  trustindexWidgetId: '',
  googleMapsUrl:
    'https://www.google.com/maps/place/Green+Torque+Hybrid%2FEV+Repair+and+Services/@38.6024175,-121.4019173,17z/data=!3m1!4b1!4m6!3m5!1s0x809adbfcb6282a75:0x39d1f42f098ed503!8m2!3d38.6024175!4d-121.4019173!16s%2Fg%2F11wmw2rnxj',
  textQuery:
    'Green Torque Hybrid/EV Repair and Services 2031 Fulton Avenue Sacramento CA 95825',
  reviewsApiUrl: 'api/google-reviews.php'
};
