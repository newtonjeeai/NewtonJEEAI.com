import { useState } from "react";

const courses = [
  { id: 1, title: "Python & Data Structures", category: "AI", level: "Beginner", progress: 75, modules: 8, completed: 6, color: "#00f5c4", icon: "🐍" },
  { id: 2, title: "Machine Learning Fundamentals", category: "AI", level: "Intermediate", progress: 40, modules: 10, completed: 4, color: "#7b61ff", icon: "🧠" },
  { id: 3, title: "Neural Networks & Deep Learning", category: "AI", level: "Advanced", progress: 0, modules: 12, completed: 0, color: "#ff6b6b", icon: "⚡" },
  { id: 4, title: "Electronics & Microcontrollers", category: "Robotics", level: "Beginner", progress: 90, modules: 6, completed: 5, color: "#ffd166", icon: "🔌" },
  { id: 5, title: "Robot Design & Simulation", category: "Robotics", level: "Intermediate", progress: 55, modules: 9, completed: 5, color: "#06d6a0", icon: "🤖" },
  { id: 6, title: "Autonomous Navigation", category: "Robotics", level: "Advanced", progress: 20, modules: 11, completed: 2, color: "#ff9f1c", icon: "🧭" },
];

const schedule = [
  { time: "10:00 AM", title: "Deep Learning Lab", mentor: "Dr. Patel", tag: "AI", color: "#7b61ff" },
  { time: "02:00 PM", title: "Robot Simulation", mentor: "Ms. Sharma", tag: "Robotics", color: "#00f5c4" },
  { time: "04:30 PM", title: "Python Code Review", mentor: "Mr. Iyer", tag: "AI", color: "#ffd166" },
];

const badges = [
  { title: "AI Novice", earned: true, icon: "🌱" },
  { title: "Code Sprinter", earned: true, icon: "⚡" },
  { title: "Bot Builder", earned: true, icon: "🤖" },
  { title: "AI Developer", earned: false, icon: "🧠" },
  { title: "ML Engineer", earned: false, icon: "🔬" },
];

const announcements = [
  { msg: "Robotics Workshop this Saturday at 11 AM — Register now!", time: "2h ago", type: "event" },
  { msg: "New AI model sandbox added to Module 5 of ML Fundamentals", time: "5h ago", type: "update" },
  { msg: "Assignment deadline extended: Neural Networks Project → Mar 10", time: "1d ago", type: "deadline" },
];

export default function StudentPortal() {
  const [activeTab, setActiveTab] = useState("dashboard");
  const [activeFilter, setActiveFilter] = useState("All");
  const [darkMode, setDarkMode] = useState(true);

  const dm = darkMode;
  const bg = dm ? "#0a0a12" : "#f0f2f8";
  const surface = dm ? "#12121f" : "#ffffff";
  const surface2 = dm ? "#1a1a2e" : "#f8f9ff";
  const border = dm ? "rgba(255,255,255,0.07)" : "rgba(0,0,0,0.08)";
  const text = dm ? "#e8e8ff" : "#1a1a2e";
  const textMuted = dm ? "#6b6b9a" : "#8888aa";
  const accent = "#7b61ff";

  const totalProgress = Math.round(courses.reduce((s, c) => s + c.progress, 0) / courses.length);
  const enrolledCourses = courses.filter(c => c.progress > 0).length;

  const filteredCourses = activeFilter === "All" ? courses : courses.filter(c => c.category === activeFilter);

  return (
    <div style={{ fontFamily: "'DM Sans', 'Segoe UI', sans-serif", background: bg, minHeight: "100vh", color: text, transition: "all 0.3s" }}>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #7b61ff44; border-radius: 4px; }
        .nav-btn { transition: all 0.2s; cursor: pointer; border: none; }
        .nav-btn:hover { background: rgba(123,97,255,0.15) !important; }
        .nav-btn.active { background: rgba(123,97,255,0.2) !important; color: #a894ff !important; }
        .course-card { transition: transform 0.25s, box-shadow 0.25s; cursor: pointer; }
        .course-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.3) !important; }
        .tab-pill { transition: all 0.2s; cursor: pointer; }
        .tab-pill:hover { opacity: 0.85; }
        .enroll-btn { transition: all 0.2s; cursor: pointer; border: none; }
        .enroll-btn:hover { transform: scale(1.04); }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
        @keyframes fadeIn { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.4s ease; }
      `}</style>

      <div style={{ display: "flex", minHeight: "100vh" }}>
        {/* Sidebar */}
        <div style={{ width: 220, background: surface, borderRight: `1px solid ${border}`, display: "flex", flexDirection: "column", padding: "0 0 24px", flexShrink: 0, position: "sticky", top: 0, height: "100vh" }}>
          {/* Logo */}
          <div style={{ padding: "24px 20px 20px", borderBottom: `1px solid ${border}` }}>
            <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
              <div style={{ width: 34, height: 34, background: "linear-gradient(135deg, #7b61ff, #00f5c4)", borderRadius: 10, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 16 }}>⚛</div>
              <div>
                <div style={{ fontFamily: "'Space Mono', monospace", fontWeight: 700, fontSize: 13, color: text, letterSpacing: "-0.3px" }}>Newton<span style={{ color: "#7b61ff" }}>JEE</span></div>
                <div style={{ fontSize: 10, color: textMuted }}>Learning Portal</div>
              </div>
            </div>
          </div>

          {/* Nav */}
          <nav style={{ padding: "16px 12px", flex: 1 }}>
            {[
              { id: "dashboard", icon: "⬡", label: "Dashboard" },
              { id: "courses", icon: "📚", label: "Courses" },
              { id: "labs", icon: "🔬", label: "Labs" },
              { id: "assignments", icon: "📝", label: "Assignments" },
              { id: "community", icon: "💬", label: "Community" },
              { id: "certificates", icon: "🏆", label: "Certificates" },
            ].map(item => (
              <button key={item.id} className={`nav-btn ${activeTab === item.id ? "active" : ""}`}
                onClick={() => setActiveTab(item.id)}
                style={{ width: "100%", display: "flex", alignItems: "center", gap: 10, padding: "10px 12px", borderRadius: 10, background: "transparent", color: activeTab === item.id ? "#a894ff" : textMuted, fontSize: 14, fontWeight: activeTab === item.id ? 600 : 400, marginBottom: 4 }}>
                <span style={{ fontSize: 16 }}>{item.icon}</span>
                {item.label}
              </button>
            ))}
          </nav>

          {/* Profile */}
          <div style={{ padding: "0 12px" }}>
            <div style={{ background: surface2, borderRadius: 12, padding: "12px 14px", display: "flex", alignItems: "center", gap: 10 }}>
              <div style={{ width: 36, height: 36, borderRadius: "50%", background: "linear-gradient(135deg, #7b61ff, #00f5c4)", display: "flex", alignItems: "center", justifyContent: "center", fontWeight: 700, fontSize: 14, color: "#fff", flexShrink: 0 }}>AK</div>
              <div style={{ overflow: "hidden" }}>
                <div style={{ fontSize: 13, fontWeight: 600, color: text, whiteSpace: "nowrap" }}>Arjun Kumar</div>
                <div style={{ fontSize: 11, color: textMuted }}>AI Novice 🌱</div>
              </div>
            </div>
            <button onClick={() => setDarkMode(!dm)} style={{ width: "100%", marginTop: 10, background: surface2, border: `1px solid ${border}`, borderRadius: 10, padding: "8px 14px", color: textMuted, fontSize: 12, cursor: "pointer", transition: "all 0.2s" }}>
              {dm ? "☀️ Light Mode" : "🌙 Dark Mode"}
            </button>
          </div>
        </div>

        {/* Main Content */}
        <div style={{ flex: 1, overflow: "auto", padding: "28px 32px" }}>

          {/* DASHBOARD TAB */}
          {activeTab === "dashboard" && (
            <div className="fade-in">
              {/* Header */}
              <div style={{ marginBottom: 28 }}>
                <h1 style={{ fontFamily: "'Space Mono', monospace", fontSize: 26, fontWeight: 700, color: text, letterSpacing: "-0.5px" }}>
                  Good morning, Arjun 👋
                </h1>
                <p style={{ color: textMuted, fontSize: 14, marginTop: 4 }}>You're 25% away from earning your next badge. Keep going!</p>
              </div>

              {/* Stats Row */}
              <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 16, marginBottom: 28 }}>
                {[
                  { label: "Overall Progress", value: `${totalProgress}%`, sub: "across all courses", color: "#7b61ff", icon: "📈" },
                  { label: "Enrolled Courses", value: enrolledCourses, sub: "of 6 total", color: "#00f5c4", icon: "📚" },
                  { label: "Badges Earned", value: "3", sub: "of 5 available", color: "#ffd166", icon: "🏅" },
                  { label: "Hours Learned", value: "42h", sub: "this month", color: "#ff6b6b", icon: "⏱" },
                ].map((stat, i) => (
                  <div key={i} style={{ background: surface, borderRadius: 16, padding: "20px 20px", border: `1px solid ${border}`, position: "relative", overflow: "hidden" }}>
                    <div style={{ position: "absolute", top: -12, right: -12, fontSize: 52, opacity: 0.07 }}>{stat.icon}</div>
                    <div style={{ fontSize: 11, color: textMuted, fontWeight: 500, textTransform: "uppercase", letterSpacing: 1, marginBottom: 8 }}>{stat.label}</div>
                    <div style={{ fontFamily: "'Space Mono', monospace", fontSize: 28, fontWeight: 700, color: stat.color }}>{stat.value}</div>
                    <div style={{ fontSize: 12, color: textMuted, marginTop: 4 }}>{stat.sub}</div>
                  </div>
                ))}
              </div>

              {/* Two column */}
              <div style={{ display: "grid", gridTemplateColumns: "1fr 320px", gap: 20 }}>
                {/* Active courses */}
                <div>
                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 16 }}>
                    <h2 style={{ fontSize: 16, fontWeight: 700, color: text }}>Active Courses</h2>
                    <button onClick={() => setActiveTab("courses")} style={{ fontSize: 12, color: accent, background: "none", border: "none", cursor: "pointer" }}>View all →</button>
                  </div>
                  <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                    {courses.filter(c => c.progress > 0).slice(0, 3).map(course => (
                      <div key={course.id} className="course-card" style={{ background: surface, borderRadius: 14, padding: "16px 18px", border: `1px solid ${border}`, display: "flex", alignItems: "center", gap: 16 }}>
                        <div style={{ width: 44, height: 44, borderRadius: 12, background: `${course.color}22`, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 22, flexShrink: 0 }}>{course.icon}</div>
                        <div style={{ flex: 1 }}>
                          <div style={{ fontWeight: 600, fontSize: 14, color: text, marginBottom: 6 }}>{course.title}</div>
                          <div style={{ background: dm ? "rgba(255,255,255,0.07)" : "rgba(0,0,0,0.07)", borderRadius: 100, height: 6, overflow: "hidden" }}>
                            <div style={{ height: "100%", width: `${course.progress}%`, background: `linear-gradient(90deg, ${course.color}, ${course.color}99)`, borderRadius: 100, transition: "width 1s ease" }} />
                          </div>
                          <div style={{ fontSize: 11, color: textMuted, marginTop: 4 }}>{course.completed}/{course.modules} modules · {course.progress}% complete</div>
                        </div>
                        <div style={{ fontFamily: "'Space Mono', monospace", fontSize: 18, fontWeight: 700, color: course.color }}>{course.progress}%</div>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Right column */}
                <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
                  {/* Schedule */}
                  <div style={{ background: surface, borderRadius: 16, padding: "18px 20px", border: `1px solid ${border}` }}>
                    <h3 style={{ fontSize: 14, fontWeight: 700, color: text, marginBottom: 14 }}>Today's Schedule</h3>
                    <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                      {schedule.map((s, i) => (
                        <div key={i} style={{ display: "flex", gap: 12, alignItems: "flex-start" }}>
                          <div style={{ fontFamily: "'Space Mono', monospace", fontSize: 10, color: textMuted, paddingTop: 3, minWidth: 52 }}>{s.time}</div>
                          <div style={{ flex: 1, background: `${s.color}15`, borderLeft: `3px solid ${s.color}`, borderRadius: "0 8px 8px 0", padding: "6px 10px" }}>
                            <div style={{ fontSize: 12, fontWeight: 600, color: text }}>{s.title}</div>
                            <div style={{ fontSize: 11, color: textMuted }}>{s.mentor}</div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>

                  {/* Announcements */}
                  <div style={{ background: surface, borderRadius: 16, padding: "18px 20px", border: `1px solid ${border}` }}>
                    <h3 style={{ fontSize: 14, fontWeight: 700, color: text, marginBottom: 14 }}>Announcements</h3>
                    <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                      {announcements.map((a, i) => (
                        <div key={i} style={{ padding: "10px 12px", background: surface2, borderRadius: 10, border: `1px solid ${border}` }}>
                          <div style={{ fontSize: 12, color: text, lineHeight: 1.5 }}>{a.msg}</div>
                          <div style={{ fontSize: 10, color: textMuted, marginTop: 4 }}>{a.time}</div>
                        </div>
                      ))}
                    </div>
                  </div>

                  {/* Badges */}
                  <div style={{ background: surface, borderRadius: 16, padding: "18px 20px", border: `1px solid ${border}` }}>
                    <h3 style={{ fontSize: 14, fontWeight: 700, color: text, marginBottom: 14 }}>Badges</h3>
                    <div style={{ display: "flex", flexWrap: "wrap", gap: 8 }}>
                      {badges.map((b, i) => (
                        <div key={i} title={b.title} style={{ width: 44, height: 44, borderRadius: 12, background: b.earned ? "linear-gradient(135deg, #7b61ff33, #00f5c433)" : surface2, border: `2px solid ${b.earned ? "#7b61ff55" : border}`, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 20, opacity: b.earned ? 1 : 0.3, cursor: "pointer", transition: "all 0.2s" }}>
                          {b.icon}
                        </div>
                      ))}
                    </div>
                    <div style={{ fontSize: 11, color: textMuted, marginTop: 10 }}>3/5 badges earned · AI Developer up next</div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* COURSES TAB */}
          {activeTab === "courses" && (
            <div className="fade-in">
              <div style={{ marginBottom: 24 }}>
                <h1 style={{ fontFamily: "'Space Mono', monospace", fontSize: 24, fontWeight: 700, color: text }}>Course Catalog</h1>
                <p style={{ color: textMuted, fontSize: 14, marginTop: 4 }}>Browse and enroll in AI & Robotics courses</p>
              </div>

              {/* Filter pills */}
              <div style={{ display: "flex", gap: 8, marginBottom: 24 }}>
                {["All", "AI", "Robotics"].map(f => (
                  <button key={f} className="tab-pill" onClick={() => setActiveFilter(f)}
                    style={{ padding: "8px 20px", borderRadius: 100, border: "none", cursor: "pointer", fontSize: 13, fontWeight: 600, background: activeFilter === f ? accent : surface, color: activeFilter === f ? "#fff" : textMuted }}>
                    {f}
                  </button>
                ))}
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 16 }}>
                {filteredCourses.map(course => (
                  <div key={course.id} className="course-card" style={{ background: surface, borderRadius: 18, border: `1px solid ${border}`, overflow: "hidden" }}>
                    <div style={{ height: 80, background: `linear-gradient(135deg, ${course.color}33, ${course.color}11)`, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 40, position: "relative" }}>
                      {course.icon}
                      <div style={{ position: "absolute", top: 12, right: 12, background: `${course.color}33`, color: course.color, fontSize: 10, fontWeight: 700, padding: "3px 10px", borderRadius: 100, border: `1px solid ${course.color}55` }}>{course.level}</div>
                    </div>
                    <div style={{ padding: "16px 18px 18px" }}>
                      <div style={{ fontSize: 11, color: course.color, fontWeight: 700, textTransform: "uppercase", letterSpacing: 1, marginBottom: 6 }}>{course.category}</div>
                      <div style={{ fontSize: 15, fontWeight: 700, color: text, marginBottom: 8, lineHeight: 1.3 }}>{course.title}</div>
                      <div style={{ fontSize: 12, color: textMuted, marginBottom: 12 }}>{course.modules} modules · {course.progress > 0 ? `${course.progress}% complete` : "Not started"}</div>

                      {course.progress > 0 && (
                        <div style={{ marginBottom: 14 }}>
                          <div style={{ background: dm ? "rgba(255,255,255,0.07)" : "rgba(0,0,0,0.07)", borderRadius: 100, height: 5, overflow: "hidden" }}>
                            <div style={{ height: "100%", width: `${course.progress}%`, background: course.color, borderRadius: 100 }} />
                          </div>
                        </div>
                      )}

                      <button className="enroll-btn" style={{ width: "100%", padding: "9px", borderRadius: 10, background: course.progress > 0 ? `${course.color}22` : accent, color: course.progress > 0 ? course.color : "#fff", fontWeight: 600, fontSize: 13 }}>
                        {course.progress > 0 ? "Continue Learning →" : "Enroll Now"}
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* LABS TAB */}
          {activeTab === "labs" && (
            <div className="fade-in">
              <div style={{ marginBottom: 24 }}>
                <h1 style={{ fontFamily: "'Space Mono', monospace", fontSize: 24, fontWeight: 700, color: text }}>Interactive Labs</h1>
                <p style={{ color: textMuted, fontSize: 14, marginTop: 4 }}>Hands-on coding environments for AI & Robotics</p>
              </div>
              <div style={{ display: "grid", gridTemplateColumns: "repeat(2, 1fr)", gap: 16 }}>
                {[
                  { title: "Python Sandbox", desc: "Write and execute Python code in a secure Docker environment. Test your ML scripts.", icon: "🐍", tag: "Python 3.11", color: "#ffd166", status: "Ready" },
                  { title: "AI Model Trainer", desc: "Train neural networks with TensorFlow/PyTorch. Visualize loss and accuracy in real-time.", icon: "🧠", tag: "TF 2.13 / PyTorch", color: "#7b61ff", status: "Ready" },
                  { title: "Robotics Simulator", desc: "Simulate robot movements using Blockly or Python. Design and test autonomous behaviors.", icon: "🤖", tag: "Blockly + Python", color: "#00f5c4", status: "Ready" },
                  { title: "Computer Vision Lab", desc: "Run OpenCV scripts, detect objects, and experiment with image processing pipelines.", icon: "👁", tag: "OpenCV 4.8", color: "#ff6b6b", status: "Beta" },
                ].map((lab, i) => (
                  <div key={i} className="course-card" style={{ background: surface, borderRadius: 18, border: `1px solid ${border}`, padding: "22px 24px" }}>
                    <div style={{ display: "flex", alignItems: "flex-start", gap: 16, marginBottom: 16 }}>
                      <div style={{ width: 52, height: 52, borderRadius: 14, background: `${lab.color}22`, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 26, flexShrink: 0 }}>{lab.icon}</div>
                      <div>
                        <div style={{ fontWeight: 700, fontSize: 16, color: text }}>{lab.title}</div>
                        <div style={{ display: "flex", gap: 6, marginTop: 4 }}>
                          <span style={{ fontSize: 10, background: `${lab.color}22`, color: lab.color, padding: "2px 8px", borderRadius: 100, fontWeight: 600 }}>{lab.tag}</span>
                          <span style={{ fontSize: 10, background: lab.status === "Ready" ? "#00f5c422" : "#ffd16622", color: lab.status === "Ready" ? "#00f5c4" : "#ffd166", padding: "2px 8px", borderRadius: 100, fontWeight: 600 }}>{lab.status}</span>
                        </div>
                      </div>
                    </div>
                    <p style={{ fontSize: 13, color: textMuted, lineHeight: 1.6, marginBottom: 18 }}>{lab.desc}</p>
                    <button className="enroll-btn" style={{ padding: "10px 20px", borderRadius: 10, background: `linear-gradient(135deg, ${lab.color}, ${lab.color}99)`, color: "#111", fontWeight: 700, fontSize: 13 }}>
                      Launch Lab ↗
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* ASSIGNMENTS TAB */}
          {activeTab === "assignments" && (
            <div className="fade-in">
              <div style={{ marginBottom: 24 }}>
                <h1 style={{ fontFamily: "'Space Mono', monospace", fontSize: 24, fontWeight: 700, color: text }}>Assignments & Projects</h1>
                <p style={{ color: textMuted, fontSize: 14, marginTop: 4 }}>Submit your work and track mentor reviews</p>
              </div>
              <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                {[
                  { title: "Build a Linear Regression Model", course: "ML Fundamentals", due: "Mar 5, 2026", status: "Submitted", score: "92/100", statusColor: "#00f5c4" },
                  { title: "Python Data Pipeline Exercise", course: "Python & Data Structures", due: "Mar 7, 2026", status: "Graded", score: "88/100", statusColor: "#7b61ff" },
                  { title: "Autonomous Robot Path Algorithm", course: "Autonomous Navigation", due: "Mar 10, 2026", status: "Pending", score: null, statusColor: "#ffd166" },
                  { title: "Neural Network Architecture Design", course: "Neural Networks", due: "Mar 12, 2026", status: "Not Started", score: null, statusColor: "#ff6b6b" },
                ].map((a, i) => (
                  <div key={i} style={{ background: surface, borderRadius: 14, padding: "18px 20px", border: `1px solid ${border}`, display: "flex", alignItems: "center", gap: 16 }}>
                    <div style={{ width: 10, height: 10, borderRadius: "50%", background: a.statusColor, flexShrink: 0 }} />
                    <div style={{ flex: 1 }}>
                      <div style={{ fontWeight: 600, fontSize: 14, color: text }}>{a.title}</div>
                      <div style={{ fontSize: 12, color: textMuted, marginTop: 3 }}>{a.course} · Due {a.due}</div>
                    </div>
                    <div style={{ textAlign: "right" }}>
                      <div style={{ fontSize: 12, fontWeight: 600, color: a.statusColor, background: `${a.statusColor}18`, padding: "4px 12px", borderRadius: 100 }}>{a.status}</div>
                      {a.score && <div style={{ fontFamily: "'Space Mono', monospace", fontSize: 14, fontWeight: 700, color: text, marginTop: 4 }}>{a.score}</div>}
                    </div>
                    {(a.status === "Pending" || a.status === "Not Started") && (
                      <button className="enroll-btn" style={{ padding: "8px 16px", borderRadius: 8, background: accent, color: "#fff", fontWeight: 600, fontSize: 12, marginLeft: 8 }}>Submit</button>
                    )}
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* CERTIFICATES TAB */}
          {activeTab === "certificates" && (
            <div className="fade-in">
              <div style={{ marginBottom: 24 }}>
                <h1 style={{ fontFamily: "'Space Mono', monospace", fontSize: 24, fontWeight: 700, color: text }}>Certificates & Achievements</h1>
                <p style={{ color: textMuted, fontSize: 14, marginTop: 4 }}>Download your completion certificates and badges</p>
              </div>
              <div style={{ display: "grid", gridTemplateColumns: "repeat(2, 1fr)", gap: 16 }}>
                {[
                  { title: "Python & Data Structures", date: "Feb 10, 2026", color: "#00f5c4", ready: true },
                  { title: "Electronics & Microcontrollers", date: "Jan 22, 2026", color: "#ffd166", ready: true },
                  { title: "Machine Learning Fundamentals", date: "In Progress", color: "#7b61ff", ready: false },
                  { title: "Robot Design & Simulation", date: "In Progress", color: "#06d6a0", ready: false },
                ].map((cert, i) => (
                  <div key={i} style={{ background: surface, borderRadius: 18, border: `2px solid ${cert.ready ? cert.color + "44" : border}`, padding: "24px", position: "relative", overflow: "hidden" }}>
                    <div style={{ position: "absolute", top: -20, right: -20, fontSize: 100, opacity: 0.04 }}>🏆</div>
                    <div style={{ fontSize: 11, color: cert.color, fontWeight: 700, textTransform: "uppercase", letterSpacing: 1, marginBottom: 10 }}>Certificate of Completion</div>
                    <div style={{ fontSize: 18, fontWeight: 700, color: text, marginBottom: 6 }}>{cert.title}</div>
                    <div style={{ fontSize: 12, color: textMuted, marginBottom: 20 }}>NewtonJEE · {cert.date}</div>
                    <button className="enroll-btn" disabled={!cert.ready} style={{ padding: "10px 20px", borderRadius: 10, background: cert.ready ? `linear-gradient(135deg, ${cert.color}, ${cert.color}99)` : surface2, color: cert.ready ? "#111" : textMuted, fontWeight: 700, fontSize: 13, opacity: cert.ready ? 1 : 0.6 }}>
                      {cert.ready ? "⬇ Download PDF" : "⏳ Complete Course"}
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* COMMUNITY TAB */}
          {activeTab === "community" && (
            <div className="fade-in">
              <div style={{ marginBottom: 24 }}>
                <h1 style={{ fontFamily: "'Space Mono', monospace", fontSize: 24, fontWeight: 700, color: text }}>Community & Support</h1>
                <p style={{ color: textMuted, fontSize: 14, marginTop: 4 }}>Ask questions, discuss ideas, and connect with mentors</p>
              </div>
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
                {[
                  { title: "Why is my model overfitting after epoch 3?", author: "Priya M.", replies: 5, tag: "ML Help", time: "30m ago", votes: 12 },
                  { title: "Best resources for learning ROS2 for robotics?", author: "Karan S.", replies: 8, tag: "Robotics", time: "2h ago", votes: 24 },
                  { title: "How to optimize PyTorch DataLoader for large datasets?", author: "Ananya T.", replies: 3, tag: "Deep Learning", time: "4h ago", votes: 7 },
                  { title: "Arduino vs Raspberry Pi for beginner robotics project?", author: "Rohan V.", replies: 11, tag: "Robotics", time: "1d ago", votes: 31 },
                ].map((post, i) => (
                  <div key={i} className="course-card" style={{ background: surface, borderRadius: 14, border: `1px solid ${border}`, padding: "18px 20px" }}>
                    <div style={{ display: "flex", gap: 8, marginBottom: 10 }}>
                      <span style={{ fontSize: 10, fontWeight: 700, background: `${accent}22`, color: accent, padding: "3px 10px", borderRadius: 100 }}>{post.tag}</span>
                    </div>
                    <div style={{ fontWeight: 600, fontSize: 14, color: text, lineHeight: 1.4, marginBottom: 10 }}>{post.title}</div>
                    <div style={{ display: "flex", alignItems: "center", gap: 12, fontSize: 11, color: textMuted }}>
                      <span>By {post.author}</span>
                      <span>·</span>
                      <span>💬 {post.replies} replies</span>
                      <span>·</span>
                      <span>▲ {post.votes}</span>
                      <span style={{ marginLeft: "auto" }}>{post.time}</span>
                    </div>
                  </div>
                ))}
              </div>
              <button className="enroll-btn" style={{ marginTop: 20, padding: "12px 28px", borderRadius: 12, background: accent, color: "#fff", fontWeight: 700, fontSize: 14 }}>
                + Post a Question
              </button>
            </div>
          )}

        </div>
      </div>
    </div>
  );
}
