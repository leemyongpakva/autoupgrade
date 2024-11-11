import ComponentAbstract from '../types/ComponentAbstract';
import Stepper from '../utils/Stepper';

export default class UpdatePage extends ComponentAbstract {
  protected stepCode = 'version-choice';

  constructor() {
    super();
  }

  public mount() {}

  public beforeDestroy() {}

  protected initStepper = () => {
    if (!window.UpdatePageStepper) {
      window.UpdatePageStepper = new Stepper();
    } else {
      window.UpdatePageStepper.setCurrentStep(this.stepCode);
    }
  };
}
