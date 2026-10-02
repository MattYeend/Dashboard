import { reactive, ref } from 'vue';

export interface ActionOptions {
    preserveScroll: boolean;
    onSuccess?: () => void;
    onFinish: () => void;
}

export function useRowAction(
    perform: (id: number, options: ActionOptions) => void,
) {
    const open = ref(false);
    const targetId = ref<number | null>(null);
    const processing = ref(false);

    function request(id: number): void {
        targetId.value = id;
        open.value = true;
    }

    function confirm(): void {
        if (targetId.value === null) {
            return;
        }

        processing.value = true;

        perform(targetId.value, {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
                open.value = false;
                targetId.value = null;
            },
        });
    }

    return reactive({ open, processing, request, confirm });
}

export function useBulkAction(
    perform: (ids: Array<number | string>, options: ActionOptions) => void,
    onSuccess?: () => void,
) {
    const open = ref(false);
    const ids = ref<Array<number | string>>([]);
    const processing = ref(false);

    function request(selected: Array<number | string>): void {
        if (!selected.length) {
            return;
        }

        ids.value = selected;
        open.value = true;
    }

    function confirm(): void {
        if (!ids.value.length) {
            return;
        }

        processing.value = true;

        perform(ids.value, {
            preserveScroll: true,
            onSuccess,
            onFinish: () => {
                processing.value = false;
                open.value = false;
                ids.value = [];
            },
        });
    }

    return reactive({ open, ids, processing, request, confirm });
}
