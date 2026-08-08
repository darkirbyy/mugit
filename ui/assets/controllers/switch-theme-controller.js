import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on the switch theme modal
 */
export default class extends Controller {
  static targets = ['button', 'light', 'dark', 'auto'];
  static values = {
    classActive: String,
    classInactive: String,
  };

  initialize() {
    this.storagePath = 'mugit/theme';
    this.classActive = this.classActiveValue.split(' ');
    this.classInactive = this.classInactiveValue.split(' ');
  }

  connect() {
    this.applyTheme();

    this.lightTarget.addEventListener('click', () => {
      this.setLocalTheme('light');
      this.applyTheme();
    });
    this.darkTarget.addEventListener('click', () => {
      this.setLocalTheme('dark');
      this.applyTheme();
    });
    this.autoTarget.addEventListener('click', () => {
      this.setLocalTheme('auto');
      this.applyTheme();
    });
  }

  setLocalTheme(theme) {
    if (theme == 'auto') {
      localStorage.removeItem(this.storagePath);
    } else {
      localStorage.setItem(this.storagePath, theme);
    }
  }

  getLocalTheme() {
    let localValue = localStorage.getItem(this.storagePath);
    if (localValue === null) {
      return 'auto';
    } else if (localValue === 'dark') {
      return 'dark';
    } else {
      return 'light';
    }
  }

  applyTheme() {
    let theme = this.getLocalTheme();

    let enableDarkMode =
      theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', enableDarkMode);

    this.buttonTargets.forEach((target) => {
      let active = target.getAttribute('data-' + this.identifier + '-target').includes(theme);
      this.classActive.forEach((className) => target.classList.toggle(className, active));
      this.classInactive.forEach((className) => target.classList.toggle(className, !active));
      // todo : use something more robust than lastChildElement
      target.lastElementChild.classList.toggle('hidden', !active);
    });
  }
}
