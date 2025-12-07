# Design Document

## Overview

PDCA Spiralは、Docker環境で動作するWebアプリケーションで、PHP、JavaScript、HTML、Tailwind CSSを使用して構築されます。チームが継続的改善サイクルを視覚的に追跡し、評価と振り返りを記録できるシステムです。螺旋階段のメタファーを用いて、繰り返しながら上昇する改善プロセスを表現します。

## Architecture

### Technology Stack

- **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL 8.x
- **Container Orchestration**: Docker Compose
- **Web Server**: Nginx
- **Development Tools**: phpMyAdmin, MailHog

### System Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Browser (Client)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   HTML/CSS   │  │  JavaScript  │  │   Tailwind   │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
                           │ HTTP/HTTPS
                           ▼
┌─────────────────────────────────────────────────────────┐
│                    Nginx (Port 8080)                     │
│                   Reverse Proxy / Static Files           │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│                    PHP-FPM Container                     │
│  ┌──────────────────────────────────────────────────┐  │
│  │  Controllers (Login, Register, Evaluation, etc.) │  │
│  ├──────────────────────────────────────────────────┤  │
│  │  Services (Auth, PDCA Cycle, Visualization)      │  │
│  ├──────────────────────────────────────────────────┤  │
│  │  Models (User, Team, Evaluation, NextAction)     │  │
│  ├──────────────────────────────────────────────────┤  │
│  │  Database Access Layer (PDO)                     │  │
│  └──────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│                MySQL Database (Port 3306)                │
│  ┌──────────────────────────────────────────────────┐  │
│  │  Tables: users, teams, evaluations,              │  │
│  │          next_actions, pdca_cycles               │  │
│  └──────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### Directory Structure

```
pdca-spiral/
├── docker/
│   ├── nginx/
│   │   ├── Dockerfile
│   │   └── default.conf
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   └── mysql/
│       ├── Dockerfile
│       ├── my.cnf
│       └── init.sql
├── src/
│   ├── assets/
│   │   ├── css/
│   │   │   └── styles.css (Tailwind output)
│   │   ├── js/
│   │   │   ├── spiral-visualization.js
│   │   │   ├── form-validation.js
│   │   │   └── common.js
│   │   └── img/
│   ├── config/
│   │   └── database.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── EvaluationController.php
│   │   ├── NextActionController.php
│   │   └── DashboardController.php
│   ├── models/
│   │   ├── User.php
│   │   ├── Team.php
│   │   ├── Evaluation.php
│   │   ├── NextAction.php
│   │   └── PDCACycle.php
│   ├── services/
│   │   ├── AuthService.php
│   │   ├── PDCACycleService.php
│   │   └── VisualizationService.php
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── register.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── evaluation/
│   │   │   ├── create.php
│   │   │   └── list.php
│   │   └── next-action/
│   │       ├── create.php
│   │       └── list.php
│   ├── utils/
│   │   ├── session.php
│   │   └── validation.php
│   └── index.php
├── docker-compose.yml
├── package.json
├── tailwind.config.js
└── README.md
```

## Components and Interfaces

### Frontend Components

#### 1. Spiral Visualization Component
- **Purpose**: 螺旋階段のような視覚表現でPDCAサイクルを表示
- **Technology**: Canvas API または SVG with JavaScript
- **Inputs**: 評価データの配列（スコア、日付、サイクル番号）
- **Outputs**: インタラクティブな螺旋グラフ
- **Key Features**:
  - 各評価ポイントを螺旋上に配置
  - ホバーで詳細情報を表示
  - サイクルごとに色分け
  - スムーズなアニメーション

#### 2. Form Validation Component
- **Purpose**: クライアントサイドでのフォーム検証
- **Inputs**: フォームデータ
- **Outputs**: 検証結果とエラーメッセージ
- **Validation Rules**:
  - 評価スコア: 0-10の範囲
  - メールアドレス: RFC準拠の形式
  - 必須フィールドの存在確認
  - パスワード強度チェック

### Backend Components

#### 1. AuthController
- **Responsibilities**:
  - ユーザー登録処理
  - ログイン認証
  - ログアウト処理
  - セッション管理
- **Methods**:
  - `register()`: 新規ユーザー登録
  - `login()`: 認証とセッション開始
  - `logout()`: セッション終了
  - `checkAuth()`: 認証状態確認

#### 2. EvaluationController
- **Responsibilities**:
  - 評価の作成
  - 評価一覧の取得
  - 評価の詳細表示
- **Methods**:
  - `create()`: 新規評価の保存
  - `list()`: チームの評価一覧取得
  - `getByTeam()`: チーム別評価取得
  - `getByCycle()`: サイクル別評価取得

#### 3. NextActionController
- **Responsibilities**:
  - ネクストアクションの作成
  - アクション一覧の取得
  - ステータス更新
- **Methods**:
  - `create()`: 新規アクション作成
  - `list()`: アクション一覧取得
  - `updateStatus()`: ステータス変更
  - `getByTeam()`: チーム別アクション取得

#### 4. DashboardController
- **Responsibilities**:
  - ダッシュボードデータの集約
  - 統計情報の計算
- **Methods**:
  - `index()`: ダッシュボード表示
  - `getStatistics()`: 統計データ取得

### Services

#### 1. AuthService
- **Purpose**: 認証ロジックの集約
- **Methods**:
  - `authenticate($username, $password)`: 認証処理
  - `hashPassword($password)`: パスワードハッシュ化
  - `verifyPassword($password, $hash)`: パスワード検証
  - `createSession($userId)`: セッション作成

#### 2. PDCACycleService
- **Purpose**: PDCAサイクルの管理
- **Methods**:
  - `getCurrentCycle($teamId)`: 現在のサイクル取得
  - `completeCycle($cycleId)`: サイクル完了
  - `createNewCycle($teamId)`: 新規サイクル作成
  - `getCycleStatistics($cycleId)`: サイクル統計取得

#### 3. VisualizationService
- **Purpose**: 可視化用データの整形
- **Methods**:
  - `prepareSpiralData($teamId)`: 螺旋グラフ用データ生成
  - `calculateAverageScore($evaluations)`: 平均スコア計算
  - `groupByCycle($evaluations)`: サイクル別グループ化

## Data Models

### Database Schema

#### users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    team_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_team_id (team_id)
);
```

#### teams Table
```sql
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_team_name (team_name)
);
```

#### pdca_cycles Table
```sql
CREATE TABLE pdca_cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    cycle_number INT NOT NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    status ENUM('active', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_cycle (team_id, cycle_number),
    INDEX idx_team_status (team_id, status)
);
```

#### evaluations Table
```sql
CREATE TABLE evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    team_id INT NOT NULL,
    cycle_id INT NOT NULL,
    score INT NOT NULL CHECK (score >= 0 AND score <= 10),
    reflection TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (cycle_id) REFERENCES pdca_cycles(id) ON DELETE CASCADE,
    INDEX idx_team_cycle (team_id, cycle_id),
    INDEX idx_created_at (created_at)
);
```

#### next_actions Table
```sql
CREATE TABLE next_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    cycle_id INT NOT NULL,
    user_id INT NOT NULL,
    description TEXT NOT NULL,
    target_date DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (cycle_id) REFERENCES pdca_cycles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_team_cycle (team_id, cycle_id),
    INDEX idx_status (status),
    INDEX idx_target_date (target_date)
);
```

### PHP Model Classes

#### User Model
```php
class User {
    private int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private int $teamId;
    
    public function validate(): array;
    public function save(): bool;
    public static function findById(int $id): ?User;
    public static function findByUsername(string $username): ?User;
    public static function findByEmail(string $email): ?User;
}
```

#### Evaluation Model
```php
class Evaluation {
    private int $id;
    private int $userId;
    private int $teamId;
    private int $cycleId;
    private int $score;
    private string $reflection;
    
    public function validate(): array;
    public function save(): bool;
    public static function findByTeam(int $teamId): array;
    public static function findByCycle(int $cycleId): array;
    public static function getAverageScore(int $cycleId): float;
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Property 1: Valid registration creates user with team
*For any* valid registration data (unique username, valid email, password, team name), submitting the registration should create a new user account with a unique team ID assigned
**Validates: Requirements 1.2, 1.5**

### Property 2: Duplicate username rejection
*For any* existing username in the system, attempting to register with that username should reject the registration and return an error
**Validates: Requirements 1.3**

### Property 3: Invalid email rejection
*For any* string that does not conform to valid email format, attempting to register with that email should reject the registration and return a validation error
**Validates: Requirements 1.4**

### Property 4: Valid credentials authenticate
*For any* user with valid credentials in the system, submitting those credentials should authenticate the user and create a session with their team ID
**Validates: Requirements 2.1, 2.5**

### Property 5: Invalid credentials rejection
*For any* credential combination that does not match an existing user, the login attempt should be rejected with an error message
**Validates: Requirements 2.2**

### Property 6: Logout terminates session
*For any* authenticated user session, performing logout should terminate the session and clear authentication state
**Validates: Requirements 2.3**

### Property 7: Protected page access control
*For any* protected page URL, attempting to access it without authentication should redirect to the login page
**Validates: Requirements 2.4**

### Property 8: Score range validation
*For any* integer outside the range 0-10, attempting to submit it as an evaluation score should reject the submission with a validation error
**Validates: Requirements 3.2**

### Property 9: Valid evaluation storage with cycle
*For any* valid evaluation (score 0-10, non-empty reflection), submitting it should store the evaluation with timestamp, user ID, team ID, and associate it with the current active PDCA cycle
**Validates: Requirements 3.3, 3.5**

### Property 10: Empty reflection rejection
*For any* string composed entirely of whitespace or empty, attempting to submit it as reflection text should reject the submission
**Validates: Requirements 3.4**

### Property 11: Team evaluations ordered by timestamp
*For any* team with multiple evaluations, querying the evaluation list should return all evaluations ordered by timestamp in ascending order
**Validates: Requirements 4.1**

### Property 12: Evaluation display completeness
*For any* evaluation, the rendered output should contain the score, reflection text, submitter name, and submission date
**Validates: Requirements 4.2**

### Property 13: Average score calculation
*For any* set of evaluations in a cycle, the calculated average score should equal the sum of scores divided by the count of evaluations
**Validates: Requirements 4.4**

### Property 14: Valid next action storage
*For any* valid next action (non-empty description, valid date), submitting it should store the action with team ID, current cycle ID, and status set to 'pending'
**Validates: Requirements 5.2, 5.4**

### Property 15: Empty description rejection
*For any* string composed entirely of whitespace or empty, attempting to submit it as action description should reject the submission
**Validates: Requirements 5.3**

### Property 16: Team actions display completeness
*For any* team, querying the action list should return all next actions with their status and target dates
**Validates: Requirements 5.5**

### Property 17: Hover displays evaluation details
*For any* evaluation point in the visualization, hovering over it should display the complete evaluation details including score and reflection
**Validates: Requirements 6.3**

### Property 18: Responsive layout adaptation
*For any* viewport width below mobile breakpoint (768px), the spiral visualization should adapt its layout for smaller screens
**Validates: Requirements 6.5**

### Property 19: Cycle completion creates new cycle
*For any* active PDCA cycle, marking it as complete should set its status to 'completed', set an end date, and create a new active cycle with incremented cycle number
**Validates: Requirements 7.1, 7.2**

### Property 20: Cycle display completeness
*For any* PDCA cycle, displaying its information should show cycle number, start date, end date (if completed), and completion status
**Validates: Requirements 7.3**

### Property 21: Cycle filtering
*For any* specific cycle ID, filtering evaluations and actions by that cycle should return only records associated with that cycle
**Validates: Requirements 7.4**

### Property 22: New team initializes cycle
*For any* newly created team, the system should automatically create the first PDCA cycle with cycle number 1 and status 'active'
**Validates: Requirements 7.5**

### Property 23: Data persistence completeness
*For any* evaluation data submitted, the stored record should include all required fields: user_id, team_id, cycle_id, score, reflection, and timestamp
**Validates: Requirements 8.1, 8.4**

### Property 24: Database error handling
*For any* database connection failure during data submission, the system should display an error message and not lose the submitted data
**Validates: Requirements 8.2**

### Property 25: Session expiry data preservation
*For any* form with unsaved data when session expires, the form data should be preserved in browser local storage
**Validates: Requirements 8.3**

### Property 26: Query relationship integrity
*For any* query retrieving evaluations with related data (user, team, cycle), all foreign key relationships should be properly joined and complete
**Validates: Requirements 8.5**

## Error Handling

### Client-Side Error Handling

1. **Form Validation Errors**
   - Display inline error messages next to invalid fields
   - Prevent form submission until all validations pass
   - Use red color scheme for error states
   - Provide clear, actionable error messages in Japanese

2. **Network Errors**
   - Display toast notifications for failed API calls
   - Implement retry mechanism for transient failures
   - Save form data to local storage before submission
   - Show loading states during async operations

3. **Session Expiry**
   - Detect session expiry before form submission
   - Preserve form data in local storage
   - Redirect to login with return URL
   - Restore form data after re-authentication

### Server-Side Error Handling

1. **Database Errors**
   - Log all database errors with context
   - Return generic error messages to client (avoid exposing schema)
   - Implement transaction rollback for multi-step operations
   - Use try-catch blocks around all database operations

2. **Validation Errors**
   - Return structured error responses with field-level details
   - Use HTTP 422 status code for validation failures
   - Include error codes for client-side handling
   - Sanitize all user inputs before validation

3. **Authentication Errors**
   - Use HTTP 401 for authentication failures
   - Use HTTP 403 for authorization failures
   - Implement rate limiting for login attempts
   - Log suspicious authentication patterns

4. **Server Errors**
   - Log all unexpected errors with stack traces
   - Return HTTP 500 with generic message
   - Implement error monitoring and alerting
   - Gracefully degrade functionality when possible

### Error Response Format

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "入力内容に誤りがあります",
    "fields": {
      "score": "スコアは0から10の範囲で入力してください",
      "reflection": "振り返りを入力してください"
    }
  }
}
```

## Testing Strategy

### Unit Testing

**Framework**: PHPUnit for PHP backend testing

**Coverage Areas**:
- Model validation methods
- Service layer business logic
- Database query methods
- Authentication and authorization logic
- Data transformation utilities

**Example Unit Tests**:
- Test User model validates email format correctly
- Test password hashing and verification
- Test evaluation score validation (boundary values: -1, 0, 10, 11)
- Test average score calculation with known inputs
- Test cycle completion state transitions
- Test empty/whitespace string rejection in validations

### Property-Based Testing

**Framework**: Eris (PHP property-based testing library)

**Configuration**:
- Minimum 100 iterations per property test
- Use appropriate generators for each data type
- Tag each test with the corresponding design property number

**Property Test Requirements**:
- Each property-based test MUST be tagged with: `@property Feature: pdca-spiral, Property {number}: {property_text}`
- Each correctness property MUST be implemented by a SINGLE property-based test
- Tests should use smart generators that constrain to valid input spaces

**Example Property Tests**:
- Property 1: Generate random valid registration data, verify user and team creation
- Property 8: Generate random integers outside 0-10, verify rejection
- Property 13: Generate random evaluation sets, verify average calculation
- Property 19: Generate random active cycles, verify completion creates new cycle

### Integration Testing

**Scope**:
- End-to-end user flows (registration → login → evaluation → visualization)
- Database transaction integrity
- Session management across requests
- API endpoint responses

**Tools**:
- Selenium or Playwright for browser automation
- Database fixtures for consistent test data
- Docker Compose for isolated test environment

### Frontend Testing

**JavaScript Testing**:
- Test spiral visualization data transformation
- Test form validation logic
- Test local storage operations
- Test responsive layout breakpoints

**Visual Regression Testing**:
- Capture screenshots of spiral visualization
- Test responsive layouts at different breakpoints
- Verify color schemes and animations

## Security Considerations

### Authentication & Authorization

1. **Password Security**
   - Use `password_hash()` with PASSWORD_BCRYPT or PASSWORD_ARGON2ID
   - Minimum password length: 8 characters
   - Store only hashed passwords, never plaintext

2. **Session Security**
   - Use secure, httpOnly cookies
   - Implement session timeout (30 minutes of inactivity)
   - Regenerate session ID after login
   - Use CSRF tokens for state-changing operations

3. **Access Control**
   - Verify team membership for all team-scoped operations
   - Check authentication on every protected endpoint
   - Implement role-based access if needed in future

### Input Validation & Sanitization

1. **SQL Injection Prevention**
   - Use PDO prepared statements for all queries
   - Never concatenate user input into SQL
   - Validate data types before database operations

2. **XSS Prevention**
   - Escape all output using `htmlspecialchars()`
   - Use Content Security Policy headers
   - Sanitize user input before storage

3. **CSRF Protection**
   - Generate unique tokens for each form
   - Validate tokens on form submission
   - Use SameSite cookie attribute

### Data Privacy

1. **Team Data Isolation**
   - Always filter queries by team_id
   - Verify user belongs to team before showing data
   - Prevent cross-team data leakage

2. **Sensitive Data**
   - Never log passwords or tokens
   - Use HTTPS in production
   - Implement proper error messages (avoid information disclosure)

## Performance Considerations

### Database Optimization

1. **Indexing Strategy**
   - Index foreign keys (team_id, user_id, cycle_id)
   - Composite index on (team_id, created_at) for evaluation queries
   - Index on status fields for filtering

2. **Query Optimization**
   - Use JOIN instead of multiple queries
   - Implement pagination for large result sets
   - Cache frequently accessed data (current cycle)

### Frontend Optimization

1. **Asset Optimization**
   - Minify CSS and JavaScript
   - Use Tailwind CSS purge for smaller bundle
   - Lazy load visualization library

2. **Rendering Performance**
   - Use requestAnimationFrame for animations
   - Debounce scroll and resize events
   - Implement virtual scrolling for long lists

## Deployment Strategy

### Docker Configuration

1. **Development Environment**
   - Hot reload for PHP and JavaScript
   - phpMyAdmin for database inspection
   - MailHog for email testing

2. **Production Considerations**
   - Remove development tools (phpMyAdmin, MailHog)
   - Use environment variables for configuration
   - Implement health check endpoints
   - Set up database backups

### Environment Variables

```
DB_HOST=db
DB_NAME=pdca_spiral
DB_USER=root
DB_PASSWORD=root
SESSION_SECRET=<random-secret>
APP_ENV=development|production
```

## Future Enhancements

1. **Team Collaboration**
   - Real-time updates using WebSockets
   - Commenting on evaluations
   - @mentions for team members

2. **Analytics**
   - Trend analysis across cycles
   - Team performance metrics
   - Export data to CSV/PDF

3. **Notifications**
   - Email reminders for pending actions
   - Slack/Discord integration
   - In-app notification system

4. **Mobile App**
   - Native iOS/Android apps
   - Push notifications
   - Offline support
