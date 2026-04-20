import json
import os

log_path = r"C:\Users\mus2a\.gemini\antigravity\brain\f86ce196-89f6-40e1-bdcc-821b02a53970\.system_generated\logs\overview.txt"
output_dir = r"c:\xampp_new\htdocs\tafwela\backend\scratch\recovery"
os.makedirs(output_dir, exist_ok=True)

steps_to_recover = {
    56: "app.blade.php",
    59: "dashboard.blade.php",
    68: "stations_index.blade.php",
    74: "_form.blade.php",
    77: "create.blade.php",
    80: "edit.blade.php",
    92: "users_index.blade.php",
    98: "show.blade.php",
    107: "updates_index.blade.php",
    116: "login.blade.php"
}

with open(log_path, "r", encoding="utf-8") as f:
    for line in f:
        try:
            data = json.loads(line)
            step = data.get("step_index")
            if step in steps_to_recover:
                tc = data["tool_calls"][0]["args"]["TargetContent"]
                filename = steps_to_recover[step]
                with open(os.path.join(output_dir, filename), "w", encoding="utf-8") as out:
                    out.write(tc)
                print(f"Recovered {filename} from step {step}")
        except Exception as e:
            continue
