import UpdatePage from './UpdatePage';
import { ProgressBar } from '../components/ProgressBar';

export default class UpdatePageUpdate extends UpdatePage {
  protected stepCode = 'update';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();

    const progressBarContainer = document.querySelector('.log-progress__bar') as HTMLElement;

    window.ProgressBar = new ProgressBar(progressBarContainer!);
  }
}
