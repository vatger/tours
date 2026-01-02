export type FlightCheck = {
    check: string;
    expected: string | number | null;
    actual: string | number | null;
    valid: boolean;
};

export type EvaluatedFlight = {
    flight_id: number | null;
    results: FlightCheck[];
    all_valid: boolean;
    time_to_enter: string | null;
};

export type CheckResult =
    | {
          leg_id: number;
          status: 'error';
          message: string;
      }
    | {
          leg_id: number;
          status: 'no_flights_found';
          all_flights: EvaluatedFlight[];
          filtered_flights: EvaluatedFlight[];
      }
    | {
          leg_id: number;
          status: 'found';
          completed_at: string | null;
          selected_flight: EvaluatedFlight | null;
          all_flights: EvaluatedFlight[];
      };
