import { ApiResponseAction } from './apiTypes';

type ProgressTrackerCallbacks = {
  onProcessResponse: (response: ApiResponseAction) => void | Promise<void>;
  onProcessEnd: (response: ApiResponseAction) => void | Promise<void>;
  onError: (response: ApiResponseAction) => void | Promise<void>;
};

type ProcessContainerCallbacks = {
  onError: () => void;
};

export type { ProgressTrackerCallbacks, ProcessContainerCallbacks };
