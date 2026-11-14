export interface AddressFormData {
    addressable_type: string;
    addressable_id: number | null;
    address_line_one: string;
    address_line_two: string;
    town: string;
    city: string;
    county: string;
    postcode: string;
    country: string;
    is_primary: boolean;
}