export type Stamp = {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    scene: string | null;
    accent_color: string | null;
    category: string | null;
    earned: boolean;
    progress: number;
    required: number;
    earned_at: string | null;
    vintage_year: number | null;
};

/** The payload flashed to the client when a stamp unlocks on check-in. */
export type EarnedStamp = Pick<
    Stamp,
    'id' | 'slug' | 'name' | 'description' | 'scene' | 'accent_color'
>;
