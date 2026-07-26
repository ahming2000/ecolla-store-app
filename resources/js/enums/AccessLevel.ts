export const VIEWER = 0
export const EDITOR = 1
export const SUPERVISOR = 2
export const ADMIN = 3

export type AccessLevel =
    | typeof VIEWER
    | typeof EDITOR
    | typeof SUPERVISOR
    | typeof ADMIN

type Translate = (key: string) => string

export const getAccessLevelOptions = (): AccessLevel[] => {
    return [VIEWER, EDITOR, SUPERVISOR]
}

export const getAccessLevelLabel = (
    translate: Translate,
    accessLevel: AccessLevel
): string => {
    switch (accessLevel) {
        case VIEWER:
            return translate('user.access-level.viewer')
        case EDITOR:
            return translate('user.access-level.editor')
        case SUPERVISOR:
            return translate('user.access-level.supervisor')
        case ADMIN:
            return translate('user.access-level.admin')
        default:
            return ''
    }
}
