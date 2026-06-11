export type UserRole = 'student' | 'librarian' | 'admin';
export type BookFormat = 'physical' | 'digital' | 'both';
export type LoanStatus = 'pending' | 'active' | 'returned' | 'cancelled' | 'overdue';

export interface Category {
    id: number;
    name: string;
    slug: string;
    books_count?: number;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
    registration_number?: string | null;
    avatar?: string | null;
    avatar_url?: string | null;
    role: UserRole;
    loans_count?: number;
}

export interface BookPage {
    id: number;
    page_number: number;
    content: string;
}

export interface Book {
    id: number;
    title: string;
    author: string;
    publisher?: string | null;
    year?: number | null;
    language: string;
    isbn?: string | null;
    description?: string | null;
    cover_path?: string | null;
    cover_url: string;
    format: BookFormat;
    total_copies: number;
    available_copies: number;
    loan_count: number;
    is_active: boolean;
    deleted_at?: string | null;
    ratings_avg_rating?: number | string | null;
    ratings_count?: number;
    categories: Category[];
    pages?: BookPage[];
}

export interface Fine {
    id: number;
    amount: string;
    paid_at?: string | null;
}

export interface Loan {
    id: number;
    type: 'reservation' | 'loan';
    status: LoanStatus;
    reserved_at?: string | null;
    due_at?: string | null;
    returned_at?: string | null;
    book: Book;
    user?: User;
    fine?: Fine | null;
}

export interface DatabaseNotification {
    id: string;
    data: {
        type: string;
        message?: string;
        message_key?: string;
        message_params?: Record<string, string | number>;
    };
    read_at?: string | null;
    created_at: string;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

export interface BookComment {
    id: number;
    book_id: number;
    user_id: number;
    parent_id: number | null;
    body: string;
    is_hidden?: boolean;
    created_at: string;
    score: number;
    user_vote: -1 | 1 | null;
    user: {
        id: number;
        name: string;
        avatar_url: string | null;
    };
    replies: BookComment[];
}

export interface RatingDistribution {
    1: number;
    2: number;
    3: number;
    4: number;
    5: number;
    total: number;
    average: number;
}
