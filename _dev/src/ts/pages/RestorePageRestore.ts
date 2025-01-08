import StepPage from './StepPage';

export default class RestorePageRestore extends StepPage {
  protected stepCode = 'restore';

  constructor() {
    super();
  }

  public mount = () => {
    this.initStepper();
  };

  public beforeDestroy = () => {};
}
