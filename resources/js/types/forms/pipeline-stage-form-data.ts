export interface PipelineStageFormData {
    title: string;
    description: string | null;
    position: number;
    background_colour: string;
    text_colour: string;
    is_won: boolean;
    is_lost: boolean;
}