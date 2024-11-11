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

type ApiResponse = ApiResponseHydration | ApiResponseNextRoute;

export type { ApiResponseHydration, ApiResponseNextRoute, ApiResponse };
