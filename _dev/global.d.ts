import Stepper from './src/ts/utils/Stepper';

interface AutoUpgradeVariables {
  token: string;
  admin_url: string;
  admin_dir: string;
  stepper_parent_id: string;
  module_version: string;
}

declare global {
  interface Window {
    AutoUpgradeVariables: AutoUpgradeVariables;
    PageStepper: ?Stepper;
  }

  const AutoUpgradeVariables: AutoUpgradeVariables;
  const PageStepper: ?Stepper;
}

export {};
