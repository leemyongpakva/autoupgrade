import UpdatePage from './UpdatePage';
import { ProgressBar } from '../components/ProgressBar';
import { LogsSummary } from '../components/LogsSummary';

export default class UpdatePageUpdate extends UpdatePage {
  protected stepCode = 'update';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();

    const progressBarContainer = document.querySelector(
      '[data-component="progress-bar"]'
    ) as HTMLElement;

    window.ProgressBar = new ProgressBar(progressBarContainer!);

    const logsSummaryContainer = document.querySelector(
      '[data-component="logs-summary"]'
    ) as HTMLElement;

    window.LogsSummary = new LogsSummary(logsSummaryContainer!);
  }
}
