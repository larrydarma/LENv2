
import React, { useState, useEffect } from "react";
import axios from "axios";

const Dashboard = () => {
  const [data, setData] = useState(null);

  useEffect(() => {
    axios.get("http://localhost:8000/").then((res) => setData(res.data.message));
  }, []);

  return (
    <div>
      <h2>Panel de Control</h2>
      <p>{data}</p>
    </div>
  );
};

export default Dashboard;
