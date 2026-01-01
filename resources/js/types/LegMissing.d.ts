export type Flight = {
    callsign: string;
    departure: string;
    destination: string;
    aircraft: string;
    departed: string;
    [key: string]: any; // in case the API returns extra fields
};

export type CheckResult =
    | {
    status: 'error';
    message: string;
}
    | {
    status: 'no_flights_found';
    all_flights: Flight[];
    filtered_flights: Flight[];
}
    | {
    status: 'found';
    completed_at: string;
    selected_flight: Flight;
    all_flights: Flight[];
    filtered_flights: Flight[];
};
