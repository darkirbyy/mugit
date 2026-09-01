import { startStimulusApp } from '@symfony/stimulus-bridge';

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(
  import.meta.webpackContext(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    {
      recursive: true,
      regExp: /\.[jt]sx?$/,
    }
  )
);

// When a turbo-frame is missing content, follow the redirection if:
//  - it's clearly a redirection
//  - the response is not ok (should not happened in prod as the turbo-force-reload is read first
//                            but useful in dev to see the symfony error page)
document.addEventListener('turbo:frame-missing', (event) => {
  if (event.detail.response.redirected || !event.detail.response.ok) {
    event.preventDefault();
    event.detail.visit(event.detail.response);
  }
});
