/**
 * Platform owner layout: SPA navigation + clinics table + plans admin.
 */
import { initSpaNavigation } from './spa-navigation';
import { initHeaderNotifications } from './header-notifications';

initSpaNavigation();
initHeaderNotifications(document);

import './platform-clinics.js';
import './admin-plans.js';
