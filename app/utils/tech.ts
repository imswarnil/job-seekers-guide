/**
 * The technologies the path teaches, with the icon and brand colour each one
 * should be drawn in.
 *
 * One registry rather than icons scattered through markdown, for two reasons.
 * Brand colours are not in the site palette and would otherwise be pasted as
 * hex into components; and Simple Icons does not ship a `java` icon — Oracle's
 * mark is trademarked — so every author who reaches for `i-simple-icons-java`
 * gets a blank box. Here, `java` maps to OpenJDK once.
 */

export interface Tech {
  /** Display name. */
  label: string
  /** Iconify name, from the locally installed collections. */
  icon: string
  /** Brand colour, used at low opacity for the tile and full for the glyph. */
  color: string
  /** One line, for the tooltip and the caption under a large thumbnail. */
  note?: string
}

export const tech = {
  'java': {
    label: 'Java',
    // Simple Icons has no `java` — the Oracle mark is trademarked. OpenJDK is
    // the icon the ecosystem actually uses, and it is the JDK you install.
    icon: 'i-simple-icons-openjdk',
    color: '#e11d48',
    note: 'The default language of this path. Most entry-level roles in this market.'
  },
  'sql': {
    label: 'SQL',
    icon: 'i-lucide-database',
    color: '#0d9488',
    note: 'Not a language you finish. A language you keep getting better at.'
  },
  'mysql': { label: 'MySQL', icon: 'i-simple-icons-mysql', color: '#00758f' },
  'postgres': { label: 'PostgreSQL', icon: 'i-simple-icons-postgresql', color: '#336791' },
  'sqlite': { label: 'SQLite', icon: 'i-simple-icons-sqlite', color: '#003b57' },
  'oracle': { label: 'Oracle', icon: 'i-simple-icons-oracle', color: '#c74634' },
  'mongodb': { label: 'MongoDB', icon: 'i-simple-icons-mongodb', color: '#47a248' },
  'html': { label: 'HTML', icon: 'i-simple-icons-html5', color: '#e34f26' },
  'css': { label: 'CSS', icon: 'i-simple-icons-css', color: '#1572b6' },
  'javascript': { label: 'JavaScript', icon: 'i-simple-icons-javascript', color: '#c79c00' },
  'typescript': { label: 'TypeScript', icon: 'i-simple-icons-typescript', color: '#3178c6' },
  'python': { label: 'Python', icon: 'i-simple-icons-python', color: '#3776ab' },
  'react': { label: 'React', icon: 'i-simple-icons-react', color: '#0d94b8' },
  'node': { label: 'Node.js', icon: 'i-simple-icons-nodedotjs', color: '#5fa04e' },
  'spring': { label: 'Spring', icon: 'i-simple-icons-spring', color: '#6db33f' },
  'maven': { label: 'Maven', icon: 'i-simple-icons-apachemaven', color: '#c71a36' },
  'git': { label: 'Git', icon: 'i-simple-icons-git', color: '#f05032' },
  'github': { label: 'GitHub', icon: 'i-simple-icons-github', color: '#6b7280' },
  'linux': { label: 'Linux', icon: 'i-simple-icons-linux', color: '#8a6d00' },
  'docker': { label: 'Docker', icon: 'i-simple-icons-docker', color: '#2496ed' },
  'salesforce': {
    label: 'Salesforce',
    icon: 'i-simple-icons-salesforce',
    color: '#00a1e0',
    note: 'Where this path led me. Not where it has to lead you.'
  },

  // The subjects that are ideas rather than products, so they take Lucide.
  'os': { label: 'Operating Systems', icon: 'i-lucide-cpu', color: '#6366f1' },
  'networks': { label: 'Networks', icon: 'i-lucide-network', color: '#8b5cf6' },
  'dsa': { label: 'Data Structures', icon: 'i-lucide-binary', color: '#0891b2' },
  'oops': { label: 'OOP', icon: 'i-lucide-boxes', color: '#7c3aed' },
  'system-design': { label: 'System Design', icon: 'i-lucide-layout-dashboard', color: '#4f46e5' },
  'interview': { label: 'Interviews', icon: 'i-lucide-messages-square', color: '#059669' },
  'resume': { label: 'Resume', icon: 'i-lucide-file-text', color: '#d97706' }
} as const satisfies Record<string, Tech>

export type TechKey = keyof typeof tech

export function findTech(key: string): Tech | undefined {
  return (tech as Record<string, Tech>)[key]
}
