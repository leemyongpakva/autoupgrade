import DomLifecycle from '../types/DomLifecycle';
import { sendUserFeedback } from '../api/sentryApi';
import { Feedback, FeedbackFields, Logs } from '../types/sentryApi';
import { logStore } from '../store/LogStore';

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

    const errors = logStore.getErrors();

    const logs: Logs = {
      logs: logStore
        .getLogs()
        .map((log) => log.message)
        .join('\n'),
      warnings: logStore
        .getWarnings()
        .map((log) => log.message)
        .join('\n'),
      errors: errors.map((log) => log.message).join('\n')
    };

    const message = errors[errors.length - 1].message;
    if (!message) {
      throw new Error('Message to send not found');
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
