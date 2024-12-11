import DomLifecycle from '../types/DomLifecycle';
import { sendUserFeedback } from '../api/sentryApi';
import { Feedback, FeedbackFields, Logs } from '../types/sentryApi';

export default class SendErrorReportDialog implements DomLifecycle {
  protected readonly formId = 'form-error-feedback';

  public mount = (): void => {
    this.#form.addEventListener('submit', this.#onSubmit);
  };

  public beforeDestroy = (): void => {
    this.#form.removeEventListener('submit', this.#onSubmit);
  };

  get #form(): HTMLFormElement {
    const form = document.forms.namedItem(this.formId);
    if (!form) {
      throw new Error('Form not found');
    }

    return form;
  }

  #onSubmit = (event: SubmitEvent) => {
    event.preventDefault();

    const logsViewer = document.querySelector('[data-component="logs-viewer"]');

    const logs: Logs = {};

    const logsContent = logsViewer?.querySelector('[data-slot-component="list"]');
    if (!logsContent) {
      throw new Error('Logs content to send not found');
    }

    const message = logsContent.lastChild?.textContent;
    if (!message) {
      throw new Error('Message to send not found');
    }

    if (!logsContent.textContent) {
      throw new Error('Logs to send not found');
    }
    logs.logs = logsContent.textContent;

    const summaryWarningText = logsViewer?.querySelector(
      '[data-summary-severity="warning"]'
    )?.textContent;
    if (summaryWarningText) {
      logs.warnings = summaryWarningText;
    }

    const summaryErrorText = logsViewer?.querySelector(
      '[data-summary-severity="error"]'
    )?.textContent;
    if (summaryErrorText) {
      logs.errors = summaryErrorText;
    }

    const feedback: Feedback = {};

    const form = event.target as HTMLFormElement;
    const formData = new FormData(form);

    Object.values(FeedbackFields).forEach((field) => {
      const value = formData.get(field);
      if (value && typeof value === 'string') {
        feedback[field] = value;
      }
    });

    sendUserFeedback(message, logs, feedback);
  };
}
