import UpdatePage from './UpdatePage';
import ProgressBar from '../components/ProgressBar';
import LogsSummary from '../components/LogsSummary';
import LogsViewer from '../components/LogsViewer';

export default class UpdatePageUpdate extends UpdatePage {
  protected stepCode = 'update';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();

    // uncomment for dev purpose

    // const progressBarContainer = document.querySelector(
    //   '[data-component="progress-bar"]'
    // ) as HTMLElement;
    //
    // window.ProgressBar = new ProgressBar(progressBarContainer!);
    //
    // const logsSummaryContainer = document.querySelector(
    //   '[data-component="logs-summary"]'
    // ) as HTMLElement;
    //
    // window.LogsSummary = new LogsSummary(logsSummaryContainer!);
    //
    // const logsViewerContainer = document.querySelector(
    //   '[data-component="logs-viewer"]'
    // ) as HTMLElement;
    //
    // window.LogsViewer = new LogsViewer(logsViewerContainer);
  }
}
