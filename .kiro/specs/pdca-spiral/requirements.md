# Requirements Document

## Introduction

PDCA Spiralは、チームが継続的な改善サイクル（Plan-Do-Check-Act）を効果的に実行するためのWebアプリケーションです。チームメンバーが定期的にチーム全体のパフォーマンスを数値で評価し、その原因や理由を言語化することで、もやもやした感情を明確にし、具体的なネクストアクションを決定できます。螺旋階段のように、繰り返しながら上昇していく改善プロセスを視覚的に表現します。

## Glossary

- **System**: PDCA Spiralアプリケーション
- **User**: チームメンバーとしてアプリケーションを使用する個人
- **Team**: 複数のUserで構成されるグループ
- **Evaluation**: チーム全体のパフォーマンスに対する0から10の数値評価
- **Reflection**: Evaluationに付随する原因・理由の記述
- **Next Action**: 改善のために決定された具体的な行動
- **PDCA Cycle**: Plan（計画）、Do（実行）、Check（評価）、Act（改善）の継続的改善サイクル
- **Team ID**: Teamを一意に識別する識別子

## Requirements

### Requirement 1

**User Story:** As a User, I want to register a new account with team information, so that I can start using the PDCA tracking system with my team.

#### Acceptance Criteria

1. WHEN a User accesses the registration page THEN the System SHALL display input fields for username, password, email, and team name
2. WHEN a User submits valid registration information THEN the System SHALL create a new User account and assign a Team ID
3. WHEN a User submits registration information with an existing username THEN the System SHALL reject the registration and display an error message
4. WHEN a User submits registration information with invalid email format THEN the System SHALL reject the registration and display a validation error
5. WHEN a new Team is created during registration THEN the System SHALL generate a unique Team ID and associate it with the User

### Requirement 2

**User Story:** As a User, I want to log in and log out of the system, so that I can securely access my team's PDCA data.

#### Acceptance Criteria

1. WHEN a User enters valid credentials on the login page THEN the System SHALL authenticate the User and redirect to the dashboard
2. WHEN a User enters invalid credentials THEN the System SHALL reject the login attempt and display an error message
3. WHEN an authenticated User clicks the logout button THEN the System SHALL terminate the session and redirect to the login page
4. WHEN a User attempts to access protected pages without authentication THEN the System SHALL redirect to the login page
5. WHEN a User successfully logs in THEN the System SHALL create a session that persists their Team ID

### Requirement 3

**User Story:** As a User, I want to submit a numerical evaluation of my team's performance with reasons, so that I can reflect on what happened and articulate unclear feelings.

#### Acceptance Criteria

1. WHEN a User accesses the evaluation form THEN the System SHALL display an input for numerical score from 0 to 10 and a text area for reflection
2. WHEN a User submits an evaluation with a score outside the 0-10 range THEN the System SHALL reject the submission and display a validation error
3. WHEN a User submits an evaluation with a valid score and reflection text THEN the System SHALL store the evaluation with timestamp, User ID, and Team ID
4. WHEN a User submits an evaluation with empty reflection text THEN the System SHALL reject the submission and require reflection input
5. WHEN an evaluation is successfully submitted THEN the System SHALL associate it with the current PDCA cycle for the Team

### Requirement 4

**User Story:** As a User, I want to view a list of all evaluations and reflections from my team, so that I can visualize our collective progress and patterns.

#### Acceptance Criteria

1. WHEN a User accesses the evaluation list page THEN the System SHALL display all evaluations from the User's Team ordered by timestamp
2. WHEN displaying each evaluation THEN the System SHALL show the score, reflection text, submitter name, and submission date
3. WHEN the evaluation list contains multiple entries THEN the System SHALL present them in a spiral staircase visual motif
4. WHEN a User views the evaluation list THEN the System SHALL calculate and display the average score for the current cycle
5. WHEN no evaluations exist for the Team THEN the System SHALL display a message encouraging the first evaluation

### Requirement 5

**User Story:** As a User, I want to define and record next actions based on our evaluations, so that we can take concrete steps toward improvement.

#### Acceptance Criteria

1. WHEN a User accesses the next action form THEN the System SHALL display an input field for action description and target date
2. WHEN a User submits a next action with valid description and date THEN the System SHALL store the action associated with the Team ID and current cycle
3. WHEN a User submits a next action without a description THEN the System SHALL reject the submission and display a validation error
4. WHEN a next action is created THEN the System SHALL mark it as pending status
5. WHEN viewing the action list THEN the System SHALL display all next actions for the Team with their status and target dates

### Requirement 6

**User Story:** As a User, I want to see visual representations of our PDCA cycles, so that I can understand our improvement trajectory over time.

#### Acceptance Criteria

1. WHEN a User views the dashboard THEN the System SHALL display evaluation scores in a spiral visualization ascending like a staircase
2. WHEN multiple PDCA cycles exist THEN the System SHALL visually distinguish between different cycles in the spiral
3. WHEN a User hovers over a point in the visualization THEN the System SHALL display the evaluation details including score and reflection
4. WHEN the spiral visualization is rendered THEN the System SHALL use smooth transitions and animations to represent continuous improvement
5. WHEN viewing the visualization on mobile devices THEN the System SHALL adapt the spiral layout for smaller screens

### Requirement 7

**User Story:** As a User, I want the system to organize evaluations into PDCA cycles, so that we can track distinct improvement iterations.

#### Acceptance Criteria

1. WHEN a Team completes a set of evaluations and next actions THEN the System SHALL allow marking the current cycle as complete
2. WHEN a cycle is marked complete THEN the System SHALL create a new cycle and associate subsequent evaluations with it
3. WHEN displaying cycle information THEN the System SHALL show the cycle number, start date, end date, and completion status
4. WHEN a User views historical data THEN the System SHALL allow filtering evaluations and actions by specific cycles
5. WHEN a new Team is created THEN the System SHALL automatically initialize the first PDCA cycle

### Requirement 8

**User Story:** As a User, I want the application to persist all data reliably, so that our team's evaluation history is never lost.

#### Acceptance Criteria

1. WHEN any data is submitted THEN the System SHALL store it in the MySQL database immediately
2. WHEN a database connection fails THEN the System SHALL display an error message and prevent data loss
3. WHEN a User's session expires THEN the System SHALL preserve any unsaved form data in browser storage
4. WHEN the System stores evaluation data THEN the System SHALL include all required fields including timestamps and foreign keys
5. WHEN querying historical data THEN the System SHALL retrieve complete records with all associated relationships intact
