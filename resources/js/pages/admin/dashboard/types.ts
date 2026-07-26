export const dashboardPeriods = [
    'daily',
    'weekly',
    'monthly',
    'yearly',
] as const

export type DashboardPeriod = (typeof dashboardPeriods)[number]

export interface DashboardFilter {
    period: DashboardPeriod
    selected_date: string
    starts_at: string
    ends_at: string
}

export interface DashboardSummary {
    completed_order_count: number
    items_sold: number
    sales_revenue: string
    canceled_order_value: string
}

export interface DashboardOverview {
    filter: DashboardFilter
    summary: DashboardSummary
}

export interface DashboardFilterSelection {
    period: DashboardPeriod
    date: string
}

export const isDashboardPeriod = (value: unknown): value is DashboardPeriod => {
    return dashboardPeriods.includes(value as DashboardPeriod)
}
