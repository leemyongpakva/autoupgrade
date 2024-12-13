import DomLifecycle from '../types/DomLifecycle';
import { sendUserFeedback } from '../api/sentryApi';
import { Feedback, FeedbackFields, Logs } from '../types/sentryApi';
import { logStore } from '../store/LogStore';
import { formatLogsMessages } from '../utils/logsUtils';

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

    const logs = this.#getLogs();
    const feedback = this.#getFeedback(event.target as HTMLFormElement);

    const latestError = logStore.getErrors().pop()?.message;
    if (!latestError) {
      throw new Error('No error message found to send');
    }

    sendUserFeedback(latestError, logs, feedback);
  };

  #getLogs(): Logs {
    return {
      logs: formatLogsMessages(logStore.getLogs()),
      warnings: formatLogsMessages(logStore.getWarnings()),
      errors: formatLogsMessages(logStore.getErrors())
    };
  }

  #getFeedback(form: HTMLFormElement): Feedback {
    const formData = new FormData(form);
    const feedback: Feedback = {};

    Object.values(FeedbackFields).forEach((field) => {
      const value = formData.get(field);
      if (value && typeof value === 'string') {
        feedback[field] = value;
      }
    });

    return feedback;
  }
}
