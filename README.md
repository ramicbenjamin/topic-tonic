# Topic Tonic · Laravel + Inertia + Vue Demo

> [!NOTE]  
> This project was created as an **exercise to showcase architectural and UI concepts** using the **Laravel Starter Kit**, Inertia.js, Vue 3, Tailwind CSS, and shadcn-vue components.
>
> You can explore more of my work at:  
> **https://ramicbenjamin.github.io**

---

## 🚀 Overview

**Topic Tonic** is a fully interactive social‑style demo application built with:

- **Laravel 11**
- **Inertia.js**
- **Vue 3 (script setup)**
- **Tailwind CSS v4**
- **shadcn‑vue UI components**
- **Modern charts (unovis + shadcn chart utilities)**

The purpose of this project is to demonstrate how to build a clean, component‑driven, analytics‑powered Laravel + Vue application with:

- Topics & discussions
- Comments
- Topic likes
- User dashboards
- Insights & analytics with charts
- Clean UI/UX patterns
- Fully dynamic data interactions via Inertia.js

---

## ✨ Features

### 📝 Topics
Users can create topics with a title & body.  
Each topic supports:

- Likes ❤️
- Comments 💬
- Author info
- Relative time (e.g. “3 hours ago”)
- Human‑friendly UI

### 🧵 Topic Details
Every topic has a dedicated page with:

- Comments
- Inline replies
- Like/unlike functionality
- Clean comment box UX

### 📊 Insights Dashboard
A dedicated analytics section that demonstrates advanced UI patterns:

- Topics over time (line chart)
- Daily likes heatmap
- Comments activity trends
- Hot topic rankings
- Total engagement summaries
- Pie charts, bar charts, and line charts

All charts are dynamic and backed by real DB data.

### 🎨 UI/UX & Components
The app uses:

- shadcn-vue components
- Tailwind v4
- Custom card highlights for “hot topics”
- Gradient backgrounds
- Skeleton-safe component structure
- Responsive grid layouts

---

## 🧱 Tech Stack

| Layer | Technology |
|------|------------|
| Backend | Laravel 11 |
| Frontend | Vue 3 (Composition API) |
| Routing | Inertia.js |
| UI | shadcn-vue + Tailwind v4 |
| Charts | Unovis + custom shadcn chart wrappers |
| Auth | Default Laravel Starter Kit authentication |
| Dev Server | Vite |

---

## 📂 Project Structure (Key Files)

```
/app
    /Http/Controllers
        TopicController.php
        CommentController.php
        LikeController.php
    /Models
/resources
    /js
        /Pages
        /Components
        /Layouts
        /routes
/database
    /factories
    /seeders
/routes
    web.php
```

---

## ⚙️ Setup & Installation

Clone the repo:

```sh
git clone https://github.com/YOUR_USERNAME/topic-tonic.git
cd topic-tonic
```

Install dependencies:

```sh
composer install
npm install
```

Setup environment:

```sh
cp .env.example .env
php artisan key:generate
```

Run migrations:

```sh
php artisan migrate --seed
```

Start the dev environment:

```sh
npm run dev
php artisan serve
```

---

## 📈 Database Seeding

The project includes advanced database seeding scripts:

- Multiple users
- Hundreds of topics
- Comments spread across time
- Likes distributed randomly
- Realistic timestamps

Use:

```sh
php artisan tinker
```

Then run the provided script from documentation.

---

## 🧪 Testing

Basic tests using Laravel's testing suite:

```sh
php artisan test
```

---

## 📹 Click to see it in action

[![Video Thumbnail](https://github.com/user-attachments/assets/ad9f7104-45ae-46ce-b7a1-788f3ffe8202)](www.youtube.com)


---

## 🤝 Contributing

This project is intended as a learning/demo application,  
but PRs, improvements, and suggestions are welcome.

---

## 📜 License

Open‑source under the MIT License.

---

## 👋 Author

**Benjamin Ramić**  
Senior Full‑Stack Product Engineer  
https://ramicbenjamin.github.io
