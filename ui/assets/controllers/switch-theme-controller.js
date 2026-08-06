import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on the switch theme modal
 */
export default class extends Controller {
  static targets = ['light', 'dark', 'auto'];

  initialize() {
    this.storagePath = 'mugit/theme';
  }

  connect() {
    this.applyTheme();

    this.lightTarget.addEventListener('click', () => {
      this.setLocalTheme('light');
    });
    this.darkTarget.addEventListener('click', () => {
      this.setLocalTheme('dark');
    });
    this.autoTarget.addEventListener('click', () => {
      this.setLocalTheme('auto');
    });
  }

  applyTheme() {
    let localValue = this.getLocalTheme();
    let enableDarkMode =
      localValue === 'dark' ||
      (localValue === null &&
        window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', enableDarkMode);
  }

  getLocalTheme() {
    return localStorage.getItem(this.storagePath);
  }

  setLocalTheme(theme) {
    if (theme == 'auto') {
      localStorage.removeItem(this.storagePath);
    } else {
      localStorage.setItem(this.storagePath, theme);
    }
    this.applyTheme();
  }
}
