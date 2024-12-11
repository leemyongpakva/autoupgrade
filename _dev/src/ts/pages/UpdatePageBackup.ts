import UpdatePage from './UpdatePage';
import ProgressTracker from '../components/ProgressTracker';
import { ApiResponseAction } from '../types/apiTypes';
import Process from '../utils/Process';
import api from '../api/RequestHandler';

export default class UpdatePageBackup extends UpdatePage {
  protected stepCode = 'backup';
  #progressTracker: ProgressTracker = new ProgressTracker(this.#progressTrackerContainer);
  #submitSkipBackupForm: null | HTMLFormElement = null;
  #submitErrorReportForm: null | HTMLFormElement = null;
  #submitRetryForm: null | HTMLFormElement = null;
  #submitRetryAlert: null | HTMLFormElement = null;

  constructor() {
    super();
  }

  public mount = async () => {
    this.initStepper();

    const stepContent = document.getElementById('ua_step_content')!;
    const updateAction = stepContent.dataset.initialProcessAction!;

    const process = new Process({
      onProcessResponse: this.#onProcessResponse,
      onProcessEnd: this.#onProcessEnd,
      onError: this.#onError
    });

    await process.startProcess(updateAction);
  };

  public beforeDestroy = () => {
    this.#progressTracker.beforeDestroy();

    this.#submitSkipBackupForm?.removeEventListener('submit', this.#handleSubmit);
    this.#submitErrorReportForm?.removeEventListener('submit', this.#handleSubmit);
    this.#submitRetryForm?.removeEventListener('submit', this.#handleSubmit);
    this.#submitRetryAlert?.removeEventListener('submit', this.#handleSubmit);
  };

  get #progressTrackerContainer(): HTMLDivElement {
    const progressTrackerContainer = document.querySelector(
      '[data-component="progress-tracker"]'
    ) as HTMLDivElement;

    if (!progressTrackerContainer) {
      throw new Error('Progress tracker container not found');
    }

    return progressTrackerContainer;
  }

  #onProcessResponse = (response: ApiResponseAction): void => {
    this.#progressTracker.updateProgress(response);
  };

  #onProcessEnd = async (response: ApiResponseAction): Promise<void> => {
    if (response.error) {
      this.#onError(response);
    } else {
      await api.post(this.#progressTrackerContainer.dataset.successRoute!);
    }
  };

  #onError = (response: ApiResponseAction): void => {
    this.#progressTracker.updateProgress(response);
    this.#progressTracker.endProgress();
    this.#displayErrorAlert();
    this.#displayErrorButtons();
  };

  #displayErrorAlert = () => {
    const alertContainer = document.getElementById('error-alert');

    if (!alertContainer) {
      throw new Error('Error alert container not found');
    }

    alertContainer.classList.remove('hidden');
  };

  #displayErrorButtons = () => {
    const buttonsContainer = document.getElementById('error-buttons');

    if (!buttonsContainer) {
      throw new Error('Error buttons container not found');
    }

    buttonsContainer.classList.remove('hidden');

    this.#submitSkipBackupForm = document.forms.namedItem('submit-skip-backup');
    this.#submitSkipBackupForm?.addEventListener('submit', this.#handleSubmit);

    this.#submitErrorReportForm = document.forms.namedItem('submit-error-report');
    this.#submitErrorReportForm?.addEventListener('submit', this.#handleSubmit);

    this.#submitRetryAlert = document.forms.namedItem('retry-alert');
    this.#submitRetryAlert?.addEventListener('submit', this.#handleSubmit);

    this.#submitRetryForm = document.forms.namedItem('retry-button');
    this.#submitRetryForm?.addEventListener('submit', this.#handleSubmit);
  };

  #handleSubmit = async (event: SubmitEvent) => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    await api.post(form.dataset.routeToSubmit!);
  };
}
