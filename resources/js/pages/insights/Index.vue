<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { show as topicsShow } from '@/routes/topics';

import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from '@/components/ui/card';

// Unovis components
import {
    VisXYContainer,
    VisLine,
    VisAxis,
    VisGroupedBar,
    VisArea,
    VisSingleContainer,
    VisDonut,
} from '@unovis/vue';

interface Stats {
    totalTopics: number;
    totalComments: number;
    totalLikes: number;
    avgCommentsPerTopic: number;
    avgLikesPerTopic: number;
}

interface ActivityItem {
    label: string;
    value: string;
}

interface TopicSummary {
    id: number;
    title: string;
    comments_count: number;
    likes_count: number;
    created_human: string;
}

interface ChartPoint {
    date: string; // YYYY-MM-DD
    topics_count: number;
    likes_count: number;
    comments_count: number;
}

const props = defineProps<{
    stats: Stats;
    activity: ActivityItem[];
    topics: TopicSummary[];
    charts: {
        per_day: ChartPoint[];
    };
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Insights', href: '/insights' },
];

const avgCommentsFormatted = computed(() =>
    Number.isFinite(props.stats.avgCommentsPerTopic)
        ? props.stats.avgCommentsPerTopic.toFixed(1)
        : '0.0',
);

const avgLikesFormatted = computed(() =>
    Number.isFinite(props.stats.avgLikesPerTopic)
        ? props.stats.avgLikesPerTopic.toFixed(1)
        : '0.0',
);

// Per-day data formatted for charts (Date objects)
const perDayData = computed(() =>
    props.charts.per_day.map((point) => ({
        date: new Date(point.date),
        topics: point.topics_count,
        likes: point.likes_count,
        comments: point.comments_count ?? 0,
    })),
);

// Engagement over time (daily + cumulative)
const engagementOverTime = computed(() => {
    let cumulative = 0;
    return perDayData.value.map((d) => {
        const dailyEngagement = d.topics + d.likes + d.comments;
        cumulative += dailyEngagement;

        return {
            date: d.date,
            daily: dailyEngagement,
            cumulative,
        };
    });
});

// Engagement split donut: Topics vs Comments vs Likes
const engagementSplit = computed(() => [
    { label: 'Topics', value: props.stats.totalTopics },
    { label: 'Comments', value: props.stats.totalComments },
    { label: 'Likes', value: props.stats.totalLikes },
]);

const totalEngagement = computed(() =>
    engagementSplit.value.reduce((sum, item) => sum + item.value, 0),
);

// Small helper for formatting dates on axis
const formatShortDate = (timestamp: number | Date) => {
    const d = new Date(timestamp);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Insights" />

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <!-- Page header -->
            <section class="flex flex-col gap-1">
                <h1 class="text-lg font-semibold tracking-tight">
                    Topic insights
                </h1>
                <p class="text-sm text-muted-foreground">
                    See how your topics are performing over time: posting
                    rhythm, hot like days, and engagement per topic.
                </p>
            </section>

            <!-- Top stats -->
            <section
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-4"
                data-test="insights-stats"
            >
                <Card
                    class="border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <CardHeader class="pb-2">
                        <CardDescription>Total topics</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ props.stats.totalTopics }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 text-xs text-muted-foreground">
                        All topics you’ve created so far.
                    </CardContent>
                </Card>

                <Card
                    class="border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <CardHeader class="pb-2">
                        <CardDescription>Total comments</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ props.stats.totalComments }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 text-xs text-muted-foreground">
                        How many replies your topics have sparked.
                    </CardContent>
                </Card>

                <Card
                    class="border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <CardHeader class="pb-2">
                        <CardDescription>Total likes</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ props.stats.totalLikes }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 text-xs text-muted-foreground">
                        Quick pulse of appreciation across all your topics.
                    </CardContent>
                </Card>

                <Card
                    class="border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <CardHeader class="pb-2">
                        <CardDescription>Avg. engagement / topic</CardDescription>
                        <CardTitle class="text-xl">
                            {{ avgCommentsFormatted }} comments ·
                            {{ avgLikesFormatted }} likes
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0 text-xs text-muted-foreground">
                        On average, how much interaction each topic gets.
                    </CardContent>
                </Card>
            </section>

            <!-- Activity & main charts -->
            <section class="grid gap-4 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
                <!-- Main charts (topics & likes over time) -->
                <div class="grid gap-4 md:grid-cols-1 xl:grid-cols-2">
                    <!-- Topics over time -->
                    <Card
                        class="border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">
                                Topics posted over time
                            </CardTitle>
                            <CardDescription>
                                Last {{ perDayData.length }} days of posting
                                activity.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="h-64">
                                <VisXYContainer
                                    :data="perDayData"
                                    :margin="{
                                        top: 10,
                                        right: 16,
                                        bottom: 30,
                                        left: 40,
                                    }"
                                    :y-domain="[0, undefined]"
                                >
                                    <VisLine
                                        :x="(d: any) => d.date"
                                        :y="(d: any) => d.topics"
                                    />
                                    <VisAxis
                                        type="x"
                                        :x="(d: any) => d.date"
                                        :tick-line="false"
                                        :domain-line="false"
                                        :grid-line="false"
                                        :tick-format="(d: number) => formatShortDate(d)"
                                    />
                                    <VisAxis
                                        type="y"
                                        :num-ticks="4"
                                        :tick-line="false"
                                        :domain-line="false"
                                    />
                                </VisXYContainer>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Likes per day -->
                    <Card
                        class="border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">
                                Hot days for likes
                            </CardTitle>
                            <CardDescription>
                                Which days attracted the most likes on your
                                topics.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="h-64">
                                <VisXYContainer
                                    :data="perDayData"
                                    :margin="{
                                        top: 10,
                                        right: 16,
                                        bottom: 30,
                                        left: 40,
                                    }"
                                    :y-domain="[0, undefined]"
                                >
                                    <VisGroupedBar
                                        :x="(d: any) => d.date"
                                        :y="(d: any) => d.likes"
                                        :rounded-corners="8"
                                    />
                                    <VisAxis
                                        type="x"
                                        :x="(d: any) => d.date"
                                        :tick-line="false"
                                        :domain-line="false"
                                        :grid-line="false"
                                        :tick-format="(d: number) => formatShortDate(d)"
                                    />
                                    <VisAxis
                                        type="y"
                                        :num-ticks="4"
                                        :tick-line="false"
                                        :domain-line="false"
                                    />
                                </VisXYContainer>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Engagement over time (area + line) -->
                <Card
                    class="border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base">
                            Engagement over time
                        </CardTitle>
                        <CardDescription>
                            Daily and cumulative engagement across topics,
                            comments and likes.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-64">
                            <VisXYContainer
                                :data="engagementOverTime"
                                :margin="{
                                    top: 10,
                                    right: 16,
                                    bottom: 30,
                                    left: 40,
                                }"
                                :y-domain="[0, undefined]"
                            >
                                <!-- Cumulative engagement as area -->
                                <VisArea
                                    :x="(d: any) => d.date"
                                    :y="(d: any) => d.cumulative"
                                />
                                <!-- Daily engagement as line -->
                                <VisLine
                                    :x="(d: any) => d.date"
                                    :y="(d: any) => d.daily"
                                />
                                <VisAxis
                                    type="x"
                                    :x="(d: any) => d.date"
                                    :tick-line="false"
                                    :domain-line="false"
                                    :grid-line="false"
                                    :tick-format="(d: number) => formatShortDate(d)"
                                />
                                <VisAxis
                                    type="y"
                                    :num-ticks="4"
                                    :tick-line="false"
                                    :domain-line="false"
                                />
                            </VisXYContainer>
                        </div>
                    </CardContent>
                </Card>

                <!-- Comments vs likes over time (multi-line) -->
                <Card
                    class="border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base">
                            Comments vs likes over time
                        </CardTitle>
                        <CardDescription>
                            Compare how often people reply versus just like.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-64">
                            <VisXYContainer
                                :data="perDayData"
                                :margin="{
                                    top: 10,
                                    right: 16,
                                    bottom: 30,
                                    left: 40,
                                }"
                                :y-domain="[0, undefined]"
                            >
                                <VisLine
                                    :x="(d: any) => d.date"
                                    :y="[
                                        (d: any) => d.comments,
                                        (d: any) => d.likes,
                                    ]"
                                />
                                <VisAxis
                                    type="x"
                                    :x="(d: any) => d.date"
                                    :tick-line="false"
                                    :domain-line="false"
                                    :grid-line="false"
                                    :tick-format="(d: number) => formatShortDate(d)"
                                />
                                <VisAxis
                                    type="y"
                                    :num-ticks="4"
                                    :tick-line="false"
                                    :domain-line="false"
                                />
                            </VisXYContainer>
                        </div>
                    </CardContent>
                </Card>

                <!-- Engagement split donut -->
                <Card
                    class="border-sidebar-border/70 dark:border-sidebar-border flex flex-col"
                >
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base">
                            Engagement split
                        </CardTitle>
                        <CardDescription>
                            How engagement breaks down across topics, comments
                            and likes.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <div class="h-64 flex items-center justify-center">
                            <VisSingleContainer
                                :data="engagementSplit"
                                :margin="{ top: 20, bottom: 20 }"
                            >
                                <VisDonut
                                    :value="(d: any) => d.value"
                                    :label="(d: any) => d.label"
                                    :arc-width="40"
                                    :central-label="totalEngagement.toLocaleString()"
                                    central-sub-label="Total"
                                />
                            </VisSingleContainer>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <section>
                <!-- Recent topics / activity table -->
                <Card
                    class="border-sidebar-border/70 dark:border-sidebar-border"
                    data-test="insights-topics-card"
                >
                    <CardHeader>
                        <CardTitle class="text-base">
                            Your topics at a glance
                        </CardTitle>
                        <CardDescription>
                            See which topics are driving the most conversation
                            and likes.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full text-left text-sm border-collapse"
                            >
                                <thead
                                    class="border-b border-border/70 text-xs text-muted-foreground uppercase tracking-wide"
                                >
                                <tr>
                                    <th class="py-2 pr-4">Topic</th>
                                    <th
                                        class="py-2 px-4 text-right"
                                    >
                                        Comments
                                    </th>
                                    <th
                                        class="py-2 px-4 text-right"
                                    >
                                        Likes
                                    </th>
                                    <th
                                        class="py-2 px-4 text-right"
                                    >
                                        Posted
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-if="!props.topics.length"
                                    class="text-sm text-muted-foreground"
                                >
                                    <td
                                        colspan="4"
                                        class="py-6 text-center"
                                    >
                                        No topics yet. Start posting to see
                                        insights here.
                                    </td>
                                </tr>
                                <tr
                                    v-for="topic in props.topics"
                                    :key="topic.id"
                                    class="border-b border-border/40 last:border-0"
                                >
                                    <td class="py-2 pr-4 align-top">
                                        <Link
                                            :href="topicsShow(topic.id).url"
                                            class="font-medium hover:underline"
                                        >
                                            {{ topic.title }}
                                        </Link>
                                    </td>
                                    <td
                                        class="py-2 px-4 text-right align-top"
                                    >
                                        {{ topic.comments_count }}
                                    </td>
                                    <td
                                        class="py-2 px-4 text-right align-top"
                                    >
                                        {{ topic.likes_count }}
                                    </td>
                                    <td
                                        class="py-2 px-4 text-right align-top text-xs text-muted-foreground whitespace-nowrap"
                                    >
                                        {{ topic.created_human }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </section>
        </div>
    </AppLayout>
</template>
