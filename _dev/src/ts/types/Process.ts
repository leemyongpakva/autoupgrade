import { ApiResponseAction } from './apiTypes';

type Callbacks = {
  onProcess: (response: ApiResponseAction) => void | Promise<void>;
  onProcessEnd: (response: ApiResponseAction) => void | Promise<void>;
  onError: (response: ApiResponseAction) => void | Promise<void>;
};

export type { Callbacks };
