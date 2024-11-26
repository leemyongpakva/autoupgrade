interface ApiResponseHydration {
  hydration: boolean;
  new_content: string;
  new_route?: string;
  add_script?: string;
  parent_to_update: string;
}

interface ApiResponseNextRoute {
  next_route: string;
}

interface ApiResponseAction {
  error: null | boolean;
  stepDone: null | boolean;
  next: string;
  status: string;
  next_desc: null | string;
  nextQuickInfo: string[];
  nextErrors: string[];
  nextParams: {
    progressPercentage: number;
    [key: string]: unknown;
  };
}

type ApiResponse = ApiResponseHydration | ApiResponseNextRoute | ApiResponseAction;

export type { ApiResponseHydration, ApiResponseNextRoute, ApiResponseAction, ApiResponse };
