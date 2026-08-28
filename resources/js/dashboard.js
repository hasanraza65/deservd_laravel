// Bundled via Vite rather than a CDN <script> tag: the admin panel runs on a
// local dev machine (XAMPP) that may have no internet access, and a CDN
// script silently failing to load left `Chart` undefined with no visible
// error to the person looking at the dashboard.
import Chart from 'chart.js/auto';

window.Chart = Chart;
