import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on the switch theme modal
 */
export default class extends Controller {
  static targets = ['button'];

  initialize() {
    this.storagePath = 'mugit/theme';
  }

  connect() {
    let localValue = localStorage.getItem(this.storagePath);
    let enableDarkMode =
      localValue === 'dark' ||
      (localValue === null &&
        window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', enableDarkMode);

    // // Whenever the user explicitly chooses light mode
    // localStorage.theme = 'light';
    // // Whenever the user explicitly chooses dark mode
    // localStorage.theme = 'dark';
    // // Whenever the user explicitly chooses to respect the OS preference
    // localStorage.removeItem('theme');
  }
}
