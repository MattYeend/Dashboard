export interface PostFormData {
    title: string;
    description: string;
    image: File | null;
    category_ids: number[];
    tag_ids: number[];
}
