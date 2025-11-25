<script setup lang="ts">
import { computed } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { show as showTopic } from '@/routes/topics';
import type { BreadcrumbItem } from '@/types';

import { Head, Link } from '@inertiajs/vue3';

import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

interface Topic {
    id: number;
    title: string;
    body: string;
    author?: string | null;
    created_human?: string;
    comments_count: number;
    likes_count: number;
}

const props = defineProps<{
    topics: Topic[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const truncate = (text: string, length = 180): string => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.slice(0, length).trimEnd() + '…';
};

// Simple "heat" score: likes are weighted a bit more than comments
const topicScore = (topic: Topic): number =>
    topic.likes_count * 2 + topic.comments_count;

// Sort topics by score desc, then by id desc (just to stabilize)
const sortedTopics = computed(() =>
    [...props.topics].sort((a, b) => {
        const scoreDiff = topicScore(b) - topicScore(a);
        if (scoreDiff !== 0) return scoreDiff;
        return b.id - a.id;
    }),
);

// Max score for highlighting hot topics
const maxScore = computed(() =>
    props.topics.length
        ? Math.max(...props.topics.map((t) => topicScore(t)))
        : 0,
);

// Top topic = first in the sorted list, if it has any activity
const isTopTopic = (topic: Topic): boolean => {
    if (!maxScore.value) return false;
    const first = sortedTopics.value[0];
    return !!first && first.id === topic.id && topicScore(topic) > 0;
};

// Hot topic = relatively close to the best one
const isHotTopic = (topic: Topic): boolean => {
    if (!maxScore.value) return false;
    const score = topicScore(topic);
    return score > 0 && score >= maxScore.value * 0.6;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Dashboard · Topic Tonic" />

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-2 rounded-xl border border-sidebar-border/70 bg-gradient-to-r from-background via-background/80 to-background/40 px-4 py-3 md:flex-row md:items-center dark:border-sidebar-border"
            >
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">
                        Topic Tonic dashboard
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        See what everyone is talking about across Topic Tonic.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Badge variant="outline" class="mt-2 w-fit md:mt-0">
                        {{ props.topics.length }} topic(s) in the system
                    </Badge>
                    <Badge
                        v-if="maxScore > 0"
                        variant="secondary"
                        class="mt-2 hidden items-center gap-1 rounded-full border border-amber-500/40 bg-amber-50/60 text-[11px] font-medium text-amber-900 md:flex dark:bg-amber-500/10 dark:text-amber-200"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500/80" />
                        Hottest topic score: {{ maxScore }}
                    </Badge>
                </div>
            </div>

            <Separator
                class="border-sidebar-border/60 dark:border-sidebar-border"
            />

            <!-- Topics list -->
            <div class="flex-1">
                <div
                    v-if="!props.topics.length"
                    class="flex h-full items-center justify-center"
                >
                    <div
                        class="max-w-sm text-center text-sm text-muted-foreground"
                    >
                        <p class="font-medium text-foreground">No topics yet</p>
                        <p class="mt-1">
                            Be the first to start the conversation from the
                            <span class="font-medium">Topics</span> page.
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="grid auto-rows-max gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <Card
                        v-for="topic in sortedTopics"
                        :key="topic.id"
                        :class="[
                            'group relative flex flex-col border bg-background/90 transition-all duration-200 hover:-translate-y-[1px] hover:shadow-md dark:border-sidebar-border',
                            isTopTopic(topic)
                                ? 'border-amber-500/50 ring-2 ring-amber-500/40 bg-gradient-to-b from-amber-50/70 via-background to-background dark:from-amber-500/10'
                                : isHotTopic(topic)
                                  ? 'border-amber-500/40 bg-gradient-to-b from-amber-50/40 via-background to-background dark:from-amber-500/5'
                                  : 'border-sidebar-border/70',
                        ]"
                    >
                        <!-- Ribbon for top/hot topics -->
                        <div
                            v-if="isTopTopic(topic)"
                            class="pointer-events-none absolute right-3 top-3 inline-flex items-center gap-1 rounded-full border border-amber-500/60 bg-amber-50/90 px-2 py-0.5 text-[11px] font-semibold text-amber-900 shadow-sm dark:bg-amber-500/20 dark:text-amber-100"
                        >
                            🔥 Most discussed
                        </div>
                        <div
                            v-else-if="isHotTopic(topic)"
                            class="pointer-events-none absolute right-3 top-3 inline-flex items-center gap-1 rounded-full border border-amber-500/40 bg-amber-50/80 px-2 py-0.5 text-[11px] font-medium text-amber-900 shadow-sm dark:bg-amber-500/15 dark:text-amber-100"
                        >
                            Hot topic
                        </div>

                        <CardHeader class="space-y-1 pb-3">
                            <CardTitle
                                class="line-clamp-2 text-base group-hover:text-foreground"
                            >
                                {{ topic.title }}
                            </CardTitle>
                            <CardDescription
                                class="flex items-center gap-2 text-xs"
                            >
                                <span
                                    v-if="topic.author"
                                    class="font-medium text-foreground"
                                >
                                    {{ topic.author }}
                                </span>
                                <span
                                    v-if="
                                        topic.author && topic.created_human
                                    "
                                    class="text-muted-foreground"
                                >
                                    ·
                                </span>
                                <span
                                    v-if="topic.created_human"
                                    class="text-muted-foreground"
                                >
                                    {{ topic.created_human }}
                                </span>
                            </CardDescription>
                        </CardHeader>

                        <CardContent
                            class="flex-1 space-y-3 pb-3 text-sm text-muted-foreground"
                        >
                            <p class="line-clamp-4">
                                {{ truncate(topic.body) }}
                            </p>

                            <div
                                class="flex items-center justify-between gap-4 rounded-md bg-muted/40 px-2 py-1.5 text-xs text-muted-foreground"
                            >
                                <div class="inline-flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-emerald-500/80"
                                        />
                                        {{ topic.comments_count }} comments
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-amber-500/80"
                                        />
                                        {{ topic.likes_count }} likes
                                    </span>
                                </div>
                                <span
                                    class="hidden text-[11px] font-medium text-muted-foreground/80 md:inline"
                                >
                                    Score: {{ topicScore(topic) }}
                                </span>
                            </div>
                        </CardContent>

                        <CardFooter
                            class="flex items-center justify-between gap-2 pt-2"
                        >
                            <Button as-child size="sm" variant="outline">
                                <Link :href="showTopic({ topic: topic.id })">
                                    View topic
                                </Link>
                            </Button>

                            <Badge
                                variant="secondary"
                                class="text-[11px] font-normal"
                            >
                                Global feed
                            </Badge>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
