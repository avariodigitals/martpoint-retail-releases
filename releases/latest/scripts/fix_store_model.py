#!/usr/bin/env python3
import sys

file_path = "/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Store_profile_model.php"
with open(file_path, 'r') as f:
    lines = f.readlines()

# Find the line containing 'default_account_id' and insert NIN fields after it
for i in range(len(lines)):
    if "default_account_id" in lines[i]:
        # Get the indentation from the beginning of this line
        line = lines[i]
        # Find where the actual content starts (after all leading whitespace)
        content_start = 0
        while content_start < len(line) and line[content_start] in ' \t':
            content_start += 1
        # The indentation is everything before the content
        indent = line[:content_start]
        
        # Build new lines with same indentation
        new_lines = [
            indent + "'nin_api_enabled'\t=>\t(isset($nin_api_enabled) && !empty($nin_api_enabled)) ? 1 : 0,\n",
            indent + "'nin_api_url'\t=>\t$nin_api_url,\n",
            indent + "'nin_api_key'\t=>\t$nin_api_key,\n",
            indent + "'nin_api_provider'\t=>\t$nin_api_provider,\n",
        ]
        
        # Insert after current line
        for j, nl in enumerate(new_lines):
            lines.insert(i + 1 + j, nl)
        
        with open(file_path, 'w') as f:
            f.writelines(lines)
        print("SUCCESS: Added NIN fields after line", i+1)
        sys.exit(0)

print("ERROR: Could not find 'default_account_id' line")
