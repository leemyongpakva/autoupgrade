import StepPage from './StepPage';

export default class RestorePagePostRestore extends StepPage {
  protected stepCode = 'post-restore';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();
  }
}
