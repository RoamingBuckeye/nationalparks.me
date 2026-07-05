import type { Meta, StoryObj } from '@storybook/vue3-vite';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';

const meta = {
    title: 'Atoms/Card',
    component: Card,
    tags: ['autodocs'],
} satisfies Meta<typeof Card>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: () => ({
        components: {
            Card,
            CardHeader,
            CardTitle,
            CardDescription,
            CardContent,
            CardFooter,
            Button,
        },
        template: `
            <Card style="max-width:22rem">
                <CardHeader>
                    <CardTitle>Yellowstone</CardTitle>
                    <CardDescription>National Park · Wyoming</CardDescription>
                </CardHeader>
                <CardContent>
                    The world's first national park — geysers, hot springs, and
                    a supervolcano beneath it all.
                </CardContent>
                <CardFooter>
                    <Button variant="outline">View park</Button>
                </CardFooter>
            </Card>`,
    }),
};
